<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\PaymentErrorHelper;
use WirecardEE\Prestashop\Helper\TransactionBuilder;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;
use WirecardEE\Prestashop\Classes\Controller\WirecardFrontController;

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
    /**
     * @var string
     */
    const REDIRECT_LINK_ORDER = 'order';

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
        $errorNotification = \Tools::getValue('error-notification');
        $errorMessages = Tools::jsonDecode($errorNotification);

        //remove the cookie if a credit card payment
        $this->context->cookie->__set('pia-enabled', false);
        $shopConfigService = new ShopConfigurationService($paymentType);

        $operation = $shopConfigService->getField('payment_action');
        $config = (new PaymentConfigurationFactory($shopConfigService))->createConfig();
        $this->transactionBuilder = new TransactionBuilder($paymentType);
        $this->translateErrorMessages($errorMessages);

        try {
            // Create order and get orderId
            $orderId = $this->determineFinalOrderId();
            $transaction = $this->transactionBuilder->buildTransaction();

            $response = $this->executeTransaction($transaction, $operation, $config);
            $this->handleTransactionResponse($response, $orderId);
        } catch (\Exception $exception) {
            $this->errors[] = $exception->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink(self::REDIRECT_LINK_ORDER));
        }
    }

    /**
     * Check if error message exists and redirects if does
     * If error message is a key, key is translated first
     *
     * @param string[] $errorMessages
     */
    private function translateErrorMessages($errorMessages)
    {
        if (count($errorMessages)) {
            $paymentErrorHelper = new PaymentErrorHelper();
            $translatedErrorMessages = $paymentErrorHelper->getTranslatedErrorMessages($errorMessages);
            foreach ($translatedErrorMessages as $errorMessage) {
                $this->errors[] = $errorMessage;
            }
            $this->redirectWithNotifications($this->context->link->getPageLink(self::REDIRECT_LINK_ORDER));
        }
    }

    /**
     * Check if we have an existing orderId or create one if required.
     *
     * @return int
     * @throws Exception
     * @since 2.0.0
     */
    private function determineFinalOrderId()
    {
        // $cartId used for cart_id within initial request
        $cartId = \Tools::getValue('cart_id');
        $orderId = Order::getIdByCartId($cartId);

        if ($orderId) {
            $this->transactionBuilder->setOrderId($orderId);
            return $orderId;
        }

        $orderId = $this->transactionBuilder->createOrder();
        return $orderId;
    }

    /**
     * Execute the transaction in the correct fashion.
     *
     * @param $transaction
     * @param $operation
     * @param $config
     * @return \Wirecard\PaymentSdk\Response\FailureResponse|\Wirecard\PaymentSdk\Response\InteractionResponse|
     * \Wirecard\PaymentSdk\Response\Response|\Wirecard\PaymentSdk\Response\SuccessResponse
     * @throws \Http\Client\Exception
     * @since 2.0.0
     */
    private function executeTransaction($transaction, $operation, $config)
    {
        $transactionService = new TransactionService($config, new WirecardLogger());
        $isSeamlessTransaction = \Tools::getValue('jsresponse');

        if ($isSeamlessTransaction) {
            return $transactionService->handleResponse(\Tools::getAllValues());
        }

        return $transactionService->process($transaction, $operation);
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
