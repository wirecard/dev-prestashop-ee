<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Classes\Engine\ReturnResponse;
use WirecardEE\Prestashop\Classes\ProcessType;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;
use WirecardEE\Prestashop\Classes\Response\Cancel;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Classes\Controller\WirecardFrontController;

/**
 * Class WirecardPaymentGatewayReturnModuleFrontController
 *
 * @extends ModuleFrontController
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayReturnModuleFrontController extends WirecardFrontController
{
    const CANCEL_PAYMENT_STATE = 'cancel';

    /** @var WirecardLogger  */
    private $logger;

    /**
     * WirecardPaymentGatewayReturnModuleFrontController constructor.
     * @since 2.1.0
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = new WirecardLogger();
    }

    /**
     * Process redirects and responses
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        $response = \Tools::getAllValues();
        $cart_id = \Tools::getValue('id_cart');
        $payment_state = \Tools::getValue('payment_state');

        try {
            $order = Order::getByCartId($cart_id);

            if ($payment_state !== Cancel::CANCEL_PAYMENT_STATE) {
                $response = $this->processRawResponse($response);
            }

            $response_factory = new ProcessablePaymentResponseFactory(
                $response,
                $order,
                ProcessType::PROCESS_RESPONSE,
                $payment_state
            );

            $processing_strategy = $response_factory->getResponseProcessing();
            $processing_strategy->process();
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error in class:'. __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $exception->getMessage()
            );
            $this->errors = $exception->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
    }

    /**
     * @param $response
     *
     * @return false|\Wirecard\PaymentSdk\Response\Response
     * @since 2.1.0
     */
    private function processRawResponse($response)
    {
        $engine_processing = new ReturnResponse();
        return $engine_processing->process($response);
    }
}
