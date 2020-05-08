<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\Transaction;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class OrderManager
 *
 * @since 1.0.0
 */
class OrderManager
{
    use TranslationHelper;

    /**
     * @var string
     * @since 2.8.0
     */
    const TRANSLATION_FILE = "ordermanager";

    const WIRECARD_OS_STARTING = 'WIRECARD_OS_STARTING';
    const WIRECARD_OS_AWAITING = 'WIRECARD_OS_AWAITING';
    const WIRECARD_OS_AUTHORIZATION = 'WIRECARD_OS_AUTHORIZATION';
    const WIRECARD_OS_PARTIALLY_REFUNDED = "WIRECARD_OS_PARTIAL_REFUNDED";

    const PHRASEAPP_KEY_OS_PAYMENT_STARTED = 'order_state_payment_started';
    const PHRASEAPP_KEY_OS_PAYMENT_AWAITING = 'order_state_payment_awaiting';
    const PHRASEAPP_KEY_OS_PAYMENT_AUTHORIZED = 'order_state_payment_authorized';
    const PHRASEAPP_KEY_OS_PARTIALLY_REFUNDED = 'order_state_payment_partially_refunded';

    const ORDER_STATE_TRANSLATION_KEY_MAP = [
        self::WIRECARD_OS_STARTING => self::PHRASEAPP_KEY_OS_PAYMENT_STARTED,
        self::WIRECARD_OS_AWAITING => self::PHRASEAPP_KEY_OS_PAYMENT_AWAITING,
        self::WIRECARD_OS_AUTHORIZATION => self::PHRASEAPP_KEY_OS_PAYMENT_AUTHORIZED,
        self::WIRECARD_OS_PARTIALLY_REFUNDED => self::PHRASEAPP_KEY_OS_PARTIALLY_REFUNDED,
    ];


    const COLOR_LIGHT_BLUE = 'lightblue';

    /** @var \Module|\WirecardPaymentGateway  */
    private $module;

    /** @var OrderService */
    private $order_service;

    /**
     * OrderManager constructor.
     *
     * @param \PaymentModule $module
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->module = \Module::getInstanceByName(\WirecardPaymentGateway::NAME);
        $this->order_service = new OrderService(null);
    }

    /**
     * Validate and create order with specific order state
     *
     * @param \Cart $cart
     * @param string $state
     * @param string $paymentType
     * @return \Order
     * @since 1.0.0
     */
    public function createOrder($cart, $state, $paymentType)
    {
        $shopConfigService = new ShopConfigurationService($paymentType);

        $this->module->validateOrder(
            $cart->id,
            \Configuration::get($state),
            $cart->getOrderTotal(true),
            $shopConfigService->getField('title'),
            null,
            array(),
            null,
            false,
            $cart->secure_key
        );
        $orderReference = $this->module->currentOrderReference;
        $this->order_service->deleteOrderPayment($orderReference);
        return $this->module->currentOrder;
    }

    /**
     * Create a new order state with specific order state name
     *
     * @param string $state
     * @since 1.0.0
     */
    public function createOrderState($state)
    {
        if (!\Configuration::get($state)) {
            $orderState = $this->initializeOrderState($state);
            $orderState->add();
        } else {
            $orderState = $this->initializeOrderState($state);
            $orderState->id = \Configuration::get($state);
            $orderState->update();
        }

        \Configuration::updateValue(
            $state,
            (int)$orderState->id
        );
    }

    /**
     * @param string $state
     * @return \OrderState
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     * @since 2.8.0
     */
    private function initializeOrderState($state)
    {
        $translationKey = $this->getTranslationKeyForOrderState($state);
        $orderState = new \OrderState();
        $orderState->name = array();
        foreach (\Language::getLanguages(false) as $language) {
            $orderStateInfo = $this->module->getTranslationForLanguage(
                $language['iso_code'],
                $translationKey,
                self::TRANSLATION_FILE
            );
            $orderState->name[$language['id_lang']] = $orderStateInfo;
        }
        $orderState->send_email = false;
        $orderState->color = self::COLOR_LIGHT_BLUE;
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = true;
        $orderState->module_name = \WirecardPaymentGateway::NAME;
        $orderState->invoice = false;

        return $orderState;
    }

    /**
     * Get translation key for specific order state
     *
     * @param $state
     * @return string
     * @throws \Exception
     * @since 2.8.0
     */
    private function getTranslationKeyForOrderState($state)
    {
        $translationKeys = self::ORDER_STATE_TRANSLATION_KEY_MAP;
        if (!isset($translationKeys[$state])) {
            throw new \Exception('Order state not exists');
        }
        return $translationKeys[$state];
    }

    /**
     * Ignore all 'check-payer-response' transaction types
     *
     * @param SuccessResponse $notification
     * @return boolean
     * @since 2.1.0
     */
    public static function isIgnorable($notification)
    {
        return $notification->getTransactionType() === Transaction::TYPE_CHECK_PAYER_RESPONSE;
    }

    /**
     * @param SuccessResponse $notification
     * @param bool $childrenEqualParent true if the sum of children equals to its own sum
     * @return mixed
     * @throws \Exception
     * @since 2.1.0
     */
    public function orderStateToPrestaShopOrderState($notification)
    {
        $backend_service = new BackendService($this->getConfig($notification), new WirecardLogger());
        $order_state = $backend_service->getOrderState($notification->getTransactionType());
        // #TEST_STATE_LIBRARY
        (new Logger())->debug("Calculated order state from {$notification->getTransactionType()}: {$order_state}");

        switch ($order_state) {
            case BackendService::TYPE_AUTHORIZED:
                return \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION);
            case BackendService::TYPE_CANCELLED:
                return _PS_OS_CANCELED_;
            case BackendService::TYPE_REFUNDED:
                return _PS_OS_REFUND_;
            case BackendService::TYPE_PROCESSING:
                return _PS_OS_PAYMENT_;
            case BackendService::TYPE_PENDING:
                return _PS_OS_PENDING_;//TODO: figure out fix
            default:
                throw new \Exception('Order state not mappable');
        }
    }

    /**
     * @param SuccessResponse $notification
     * @return string
     * @since 2.1.0
     */
    public function getTransactionState($notification)
    {
        $backend_service = new BackendService($this->getConfig($notification), new WirecardLogger());
        $transactionType = $notification->getTransactionType();

        //TODO: use just isFinal, once ticket TPWDCEE-5668 is solved in the SDK
        if ($backend_service->isFinal($transactionType) || $transactionType == Transaction::TYPE_REFUND_PURCHASE) {
            return 'closed';
        }
        return 'open';
    }

    /**
     * @param SuccessResponse $notification
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 2.1.0
     */
    private function getConfig($notification)
    {
        $payment_type = $notification->getPaymentMethod();
        $shop_config = new ShopConfigurationService($payment_type);
        return (new PaymentConfigurationFactory($shop_config))->createConfig();
    }
}
