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

    const ORDER_STATE_TRANSLATION_KEY_MAP = [
        self::WIRECARD_OS_STARTING => 'order_state_payment_started',
        self::WIRECARD_OS_AWAITING => 'order_state_payment_awaiting',
        self::WIRECARD_OS_AUTHORIZATION => 'order_state_payment_authorized'
    ];

    const COLOR_LIGHT_BLUE = 'lightblue';

    /** @var \Module|\WirecardPaymentGateway  */
    private $module;

    /**
     * OrderManager constructor.
     *
     * @param \PaymentModule $module
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->module = \Module::getInstanceByName(\WirecardPaymentGateway::NAME);
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
        $orderState = new \OrderState();
        $orderState->name = array();
        foreach (\Language::getLanguages(false) as $language) {
            $orderState->name[$language['id_lang']] = $this->getOrderStateTranslation(
                $language['iso_code'],
                $state,
                self::TRANSLATION_FILE
            );
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
     * @param $lang
     * @param $orderState
     * @param $file
     * @return string
     * @throws \Exception
     */
    private function getOrderStateTranslation($lang, $orderState, $file)
    {
        switch ($orderState) {
            case self::WIRECARD_OS_STARTING:
                return $this->module->getTranslationForLanguage($lang, 'order_state_payment_started', $file);
            case self::WIRECARD_OS_AUTHORIZATION:
                return $this->module->getTranslationForLanguage($lang, 'order_state_payment_authorized', $file);
            case self::WIRECARD_OS_AWAITING:
                return $this->module->getTranslationForLanguage($lang, 'order_state_payment_awaiting', $file);
            default:
                throw new \Exception('Order state not exists');
        }
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
