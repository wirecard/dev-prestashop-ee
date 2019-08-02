<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

use WirecardEE\Prestashop\classes\EngineResponseProcessing\ReturnPaymentEngineResponseProcessing;
use WirecardEE\Prestashop\classes\ResponseProcessing\CancelResponseProcessing;
use WirecardEE\Prestashop\classes\ResponseProcessing\ResponseProcessingFactory;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

/**
 * Class WirecardPaymentGatewayReturnModuleFrontController
 *
 * @extends ModuleFrontController
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayReturnModuleFrontController extends ModuleFrontController
{
    const CANCEL_PAYMENT_STATE = 'cancel';

    /** @var WirecardLogger  */
    public $logger;

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
     * @since 1.0.0
     */
    public function postProcess()
    {
        $response = $_REQUEST;
        $this->isCancelResponse(\Tools::getValue('payment_state'));

        try {
            $engine_processing = new ReturnPaymentEngineResponseProcessing();
            $processed_return  = $engine_processing->process($response, $this);

            //@TODO this is just here to see the result of a processed response
            $this->logger->debug('return: <pre>' . print_r($processed_return, true). '</pre>');
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
     * @param string $payment_state
     */
    private function isCancelResponse($payment_state)
    {
        if ($payment_state === self::CANCEL_PAYMENT_STATE) {
            $response_processing = new CancelResponseProcessing();
            $response_processing->process(null);
        }
    }
}
