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

use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\TransactionBuilder;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;

/**
 * Class WirecardPaymentGatewayPaymentModuleFrontController
 *
 * @extends ModuleFrontController
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayPaymentModuleFrontController extends ModuleFrontController
{
    /** @var TransactionBuilder */
    private $transactionBuilder;

    /**
     * Process payment via transaction service
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        //remove the cookie if a credit card payment
        $this->context->cookie->__set('pia-enabled', false);
        $cartId = \Tools::getValue('order_number');
        $cart = new Cart($cartId);

        $paymentType = \Tools::getValue('payment_type');
        $operation = $this->module->getConfigValue($paymentType, 'payment_action');
        $payment = $this->module->getPaymentFromType($paymentType);
        $config = $payment->createPaymentConfig($this->module);

        $this->transactionBuilder = new TransactionBuilder($this->module, $this->context, $cart->id, $paymentType);
        // Create order and get orderId
        $orderId = $this->determineFinalOrderId();

        try {
            $transaction = $this->transactionBuilder->buildTransaction();
            $this->executeTransaction($transaction, $operation, $config, $cart, $orderId);
        } catch (\Exception $exception) {
            $this->errors = $exception->getMessage();
            $this->processFailure($orderId);
        }
    }

    /**
     * Check if we have an existing orderId or create one if required.
     *
     * @return int
     * @since 2.0.0
     */
    private function determineFinalOrderId()
    {
        // $cartId used for order_number within intial request
        $cartId = \Tools::getValue('order_number');
        $orderId = Order::getIdByCartId($cartId);

        if ($orderId) {
            $this->transactionBuilder->setOrderId($orderId);
            return $orderId;
        } else {
            $orderId = $this->transactionBuilder->createOrder();
            return $orderId;
        }
    }

    /**
     * Execute the transaction in the correct fashion.
     *
     * @param $transaction
     * @param $operation
     * @param $config
     * @param $cart
     * @param $orderId
     * @since 2.0.0
     */
    private function executeTransaction($transaction, $operation, $config, $cart, $orderId)
    {
        $isSeamlessTransaction = \Tools::getValue('jsresponse');
        if ($isSeamlessTransaction) {
            return $this->executeSeamlessTransaction($_POST, $config, $cart, $orderId);
        }
        return $this->executeDefaultTransaction($transaction, $config, $operation, $orderId);
    }

    /**
     * Execute transactions with operation pay and reserve
     *
     * @param Transaction $transaction
     * @param \Wirecard\PaymentSdk\Config\Config $config
     * @param string $operation
     * @param int $orderId
     * @since 2.0.0
     */
    private function executeDefaultTransaction($transaction, $config, $operation, $orderId)
    {
        $transactionService = new TransactionService($config, new WirecardLogger());
        try {
            /** @var \Wirecard\PaymentSdk\Response\Response $response */
            $response = $transactionService->process($transaction, $operation);
            $this->handleTransactionResponse($response, $orderId);
        } catch (Exception $exception) {
            $this->errors = $exception->getMessage();
            $this->processFailure($orderId);
        }
    }

    /**
     * Execute a seamless form transaction
     *
     * @param $data
     * @param $config
     * @param $cart
     * @param $orderId
     * @since 2.0.0
     */
    private function executeSeamlessTransaction($data, $config, $cart, $orderId)
    {
        $paymentType = \Tools::getValue('payment_type');
        $redirectUrl =  $this->module->createRedirectUrl($orderId, $paymentType, 'success', $cart->id);
        $transactionService = new TransactionService($config, new WirecardLogger());

        try {
            $response = $transactionService->processJsResponse($data, $redirectUrl);
            $this->handleTransactionResponse($response, $orderId);
        } catch (Exception $exception) {
            $this->errors = $exception->getMessage();
            $this->processFailure($orderId);
        }
    }

    /**
     * Handle the response of the transaction appropriately.
     *
     * @param Wirecard\PaymentSdk\Response\Response $response
     * @param int $order_id
     * @since 2.0.0
     */
    private function handleTransactionResponse($response, $order_id)
    {
        $order = new \Order((int) $order_id);
        $response_factory = new ProcessablePaymentResponseFactory($response, $order);
        $processing_strategy = $response_factory->getResponseProcessing();
        $processing_strategy->process();
    }
}
