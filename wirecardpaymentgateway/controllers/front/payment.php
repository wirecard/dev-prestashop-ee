<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\TransactionBuilder;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;
use WirecardEE\Prestashop\Classes\Controller\WirecardFrontController;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

/**
 * Class WirecardPaymentGatewayPaymentModuleFrontController
 *
 * @extends ModuleFrontController
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayPaymentModuleFrontController extends WirecardFrontController
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
        $paymentType = \Tools::getValue('payment_type');
        //remove the cookie if a credit card payment
        $this->context->cookie->__set('pia-enabled', false);
        $shopConfigService = new ShopConfigurationService($paymentType);
        $cartId = \Tools::getValue('order_number');
        $cart = new Cart($cartId);

        $operation = $shopConfigService->getField('payment_action');
        $config = (new PaymentConfigurationFactory($shopConfigService))->createConfig();
        $this->transactionBuilder = new TransactionBuilder($paymentType);
        // Create order and get orderId
        $orderId = $this->determineFinalOrderId();
        
        try {
            $transaction = $this->transactionBuilder->buildTransaction();
            $this->executeTransaction($transaction, $operation, $config, $cart, $orderId);
        } catch (\Exception $exception) {
            $this->errors[] = $exception->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
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
            $this->errors[] = $exception->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
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
            $this->errors[] = $exception->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
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
