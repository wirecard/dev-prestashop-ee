<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\FailureResponse;

/**
 * Class ProcessablePaymentResponseFactory
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
class ProcessablePaymentResponseFactory
{
    /** @var Response|false */
    private $response;

    /** @var \Order  */
    private $order;

    /** @var string */
    private $order_state;

    /**
     * ResponseProcessingFactory constructor.
     *
     * @param Response $response
     * @param \Order $order
     * @param string $order_state
     * @since 2.1.0
     */
    public function __construct($response, $order, $order_state = null)
    {
        $this->order = $order;
        $this->order_state = $order_state;
        $this->response = $response;
    }

    /**
     * @return ProcessablePaymentResponse
     * @since 2.1.0
     */
    public function getResponseProcessing()
    {
        if ($this->isCancelResponse($this->order_state)) {
            return new Cancel($this->order);
        }

        switch (true) {
            case $this->response instanceof SuccessResponse:
                return new Success($this->order, $this->response);
            case $this->response instanceof InteractionResponse:
                return new Redirect($this->response);
            case $this->response instanceof FormInteractionResponse:
                return new FormPost($this->response);
            case $this->response instanceof FailureResponse:
            default:
                return new Failure($this->order, $this->response);
        }
    }

    /**
     * @param string $order_state
     *
     * @return bool
     * @since 2.1.0
     */
    private function isCancelResponse($order_state)
    {
        return $order_state === Cancel::CANCEL_PAYMENT_STATE;
    }
}
