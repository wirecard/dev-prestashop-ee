<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Classes\ProcessablePaymentFactory;
use WirecardEE\Prestashop\Classes\ProcessType;
use WirecardEE\Prestashop\Classes\Response\Initial\Failure as InitialFailure;
use WirecardEE\Prestashop\Classes\Response\Initial\Success as InitialSuccess;
use WirecardEE\Prestashop\Classes\Response\PostProcessing\Failure as PostProcessingFailure;
use WirecardEE\Prestashop\Classes\Response\PostProcessing\Success as PostProcessingSuccess;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class ProcessablePaymentResponseFactory
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
class ProcessablePaymentResponseFactory extends ProcessablePaymentFactory
{
    /** @var SuccessResponse|FailureResponse|InteractionResponse|FormInteractionResponse */
    private $response;

    /** @var \Order */
    private $order;

    /** @var string */
    private $orderState;

    /** @var string */
    private $processType;

    /**
     * ResponseProcessingFactory constructor.
     *
     * @param Response $response
     * @param \Order $order
     * @param string $processType
     * @param string $orderState
     * @since 2.1.0
     */
    public function __construct($response, $order, $processType = ProcessType::PROCESS_RESPONSE, $orderState = null)
    {
        $this->order = $order;
        $this->orderState = $orderState;
        $this->processType = $processType;
        $this->response = $response;
    }



    /**
     * @return ProcessablePaymentResponse
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException
     * @since 2.1.0
     */
    public function getResponseProcessing()
    {
        if ($this->isCancelResponse($this->orderState)) {
            return new Cancel($this->order);
        }

        switch (true) {
            case $this->response instanceof SuccessResponse:
                if ($this->isPostProcessing($this->response)) {
                    return new PostProcessingSuccess($this->order, $this->response);
                }
                return new InitialSuccess($this->order, $this->response);
            case $this->response instanceof InteractionResponse:
                return new Redirect($this->response);
            case $this->response instanceof FormInteractionResponse:
                return new FormPost($this->response);
            case $this->response instanceof FailureResponse:
            default:
                $order_service = new OrderService($this->order);
                if ($this->isPostProcessing($this->response)) {
                    return new PostProcessingFailure($order_service, $this->response);
                }
                return new InitialFailure($order_service, $this->response);
        }
    }

    /**
     * @param string $orderState
     *
     * @return bool
     * @since 2.1.0
     */
    private function isCancelResponse($orderState)
    {
        return $orderState === Cancel::CANCEL_PAYMENT_STATE;
    }
}
