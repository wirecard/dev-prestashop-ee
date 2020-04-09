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
use WirecardEE\Prestashop\Helper\TransactionBuilder;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;
use WirecardEE\Prestashop\Classes\Controller\WirecardFrontController;
use WirecardEE\Prestashop\Helper\TranslationHelper;

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
    use TranslationHelper;

    /**
     * @var string
     *
     * @since 2.10.0
     */
    const TRANSLATION_FILE = 'payment';

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
        $errorMessages = json_decode(urldecode(\Tools::getValue('error-notification')));

        //remove the cookie if a credit card payment
        $this->context->cookie->__set('pia-enabled', false);
        $shopConfigService = new ShopConfigurationService($paymentType);

        $operation = $shopConfigService->getField('payment_action');
        $config = (new PaymentConfigurationFactory($shopConfigService))->createConfig();
        $this->transactionBuilder = new TransactionBuilder($paymentType);

        try {
            $this->determineErrorException($this->getTranslatedErrorMessages($errorMessages));
            // Create order and get orderId
            $orderId = $this->determineFinalOrderId();
            $transaction = $this->transactionBuilder->buildTransaction();

            $response = $this->executeTransaction($transaction, $operation, $config);
            $this->handleTransactionResponse($response, $orderId);
        } catch (\Exception $exception) {
            $this->errors[] = $exception->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
    }

     /**
     * Check the notification field for an exception trigger
     *
     * @param array $errorMessages
     *
     * @return boolean
     * @throws Exception
     * @since 2.7.0
     */
    private function determineErrorException($errorMessages)
    {
        if (count($errorMessages)) {
            throw new Exception(implode(", ", $errorMessages));
        }
        return true;
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

    /**
     * @param array $errorMessages
     * @return array
     *
     * @since 2.10.0
     */
    private function getTranslatedErrorMessages($errorMessages)
    {
        $translatedMessages = array();
        foreach ($errorMessages as $errorMessage) {
            array_push(
                $translatedMessages,
                $this->translateErrorMessage(
                    $errorMessage,
                    $this->getUserFrontendLanguage()
                )
            );
        }
        return $translatedMessages;
    }

    /**
     * Translate error message key
     * @param $errorMessageKey
     * @param $lang_code
     * @return string
     *
     * @since 2.10.0
     */
    private function translateErrorMessage($errorMessageKey, $lang_code)
    {
        switch ($errorMessageKey) {
            case 'error_message_generic':
                return $this->getTranslatedString('error_message_generic', $lang_code);
            default:
                return $errorMessageKey;
        }
    }

    /**
     * Get frontend language from current user
     * @return string
     *
     * @since 2.10.0
     */
    private function getUserFrontendLanguage()
    {
        global $cookie;
        $id_lang = $cookie->id_lang;
        foreach (Language::getLanguages() as $language) {
            if ($id_lang === intval($language["id_lang"])) {
                return $language['iso_code'];
            }
        }
        return 'en';
    }
}
