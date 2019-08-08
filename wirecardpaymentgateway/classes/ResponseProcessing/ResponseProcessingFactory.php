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

namespace WirecardEE\Prestashop\Classes\ResponseProcessing;

use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\Response;
use WirecardEE\Prestashop\Classes\EngineResponseProcessing\ReturnPaymentEngineResponseProcessing;

/**
 * Class ResponseProcessingFactory
 * @package WirecardEE\Prestashop\Classes\ResponseProcessing
 * @since 2.1.0
 */
class ResponseProcessingFactory
{
    /** @var  */
    private $response;

    /** @var \Order  */
    private $order;

    /** @var string */
    private $order_state;

    /**
     * ResponseProcessingFactory constructor.
     *
     * @param $response
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
     * @return ResponseProcessing
     * @since 2.1.0
     */
    public function getResponseProcessing($logger)
    {
        if ($this->isCancelResponse($this->order_state)) {
            return new CancelResponseProcessing($this->order);
        }

        $processed_return = $this->getProcessedReturn($logger);
        //var_dump($processed_return);die();

        switch (true) {
            case $processed_return instanceof SuccessResponse:
                return new SuccessResponseProcessing($this->order, $processed_return);
            case $processed_return instanceof InteractionResponse:
                return new InteractionResponseProcessing($processed_return);
            case $processed_return instanceof FormInteractionResponse:
                return new FormInteractionResponseProcessing($processed_return);
            case $processed_return instanceof FailureResponse:
            default:
                return new FailureResponseProcessing($this->order, $processed_return);
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
        if ($order_state === 'cancel') {
            return true;
        }

        return false;
    }

    private function getProcessedReturn($logger)
    {
        $engine_processing = new ReturnPaymentEngineResponseProcessing();
        return $engine_processing->process($this->response, $logger);
    }
}
