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
    const WIRECARD_OS_STARTING = 'WIRECARD_OS_STARTING';
    const WIRECARD_OS_AWAITING = 'WIRECARD_OS_AWAITING';
    const WIRECARD_OS_AUTHORIZATION = 'WIRECARD_OS_AUTHORIZATION';

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
     * @param string $stateName
     * @since 1.0.0
     */
    public function createOrderState($stateName)
    {
        if (!\Configuration::get($stateName)) {
            $orderStateInfo = $this->getOrderStateInfo($stateName);
            $orderState = new \OrderState();
            $orderState->name = array();
            foreach (\Language::getLanguages() as $language) {
                if (\Tools::strtolower($language['iso_code']) == 'de') {
                    $orderState->name[$language['id_lang']] = $orderStateInfo['de'];
                } else {
                    $orderState->name[$language['id_lang']] = $orderStateInfo['en'];
                }
            }
            $orderState->send_email = false;
            $orderState->color = 'lightblue';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->module_name = 'wirecardpaymentgateway';
            $orderState->invoice = false;
            $orderState->add();

            \Configuration::updateValue(
                $stateName,
                (int)($orderState->id)
            );
        }
    }

    /**
     * Getter for language texts to specific order state
     *
     * @param $stateName
     * @return array
     * @since 1.0.0
     */
    private function getOrderStateInfo($stateName)
    {
        switch ($stateName) {
            case self::WIRECARD_OS_STARTING:
                return array(
                    'de' => 'Wirecard Bezahlung started',
                    'en' => 'Wirecard payment started',
                );
            case self::WIRECARD_OS_AUTHORIZATION:
                return array(
                    'de' => 'Wirecard Bezahlung authorisiert',
                    'en' => 'Wirecard payment authorized',
                );
            case self::WIRECARD_OS_AWAITING:
            default:
                return array(
                    'de' => 'Wirecard Bezahlung ausständig',
                    'en' => 'Wirecard payment awaiting'
                );
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
        return $notification->getTransactionType() === 'check-payer-response';
    }

    /**
     * @param SuccessResponse $notification
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
                return __PS_OS_PENDING_;
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

        if ($backend_service->isFinal($notification->getTransactionType())) {
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
