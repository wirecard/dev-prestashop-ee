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

namespace WirecardEE\Prestashop\Classes\Notification;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Response\SuccessResponse;

use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Services\ShopConfigurationService;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Models\Transaction;

/**
 * Class Success
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
final class Success implements ProcessablePaymentNotification
{
    /** @var \Order */
    private $order;

    /** @var SuccessResponse */
    private $notification;

    /** @var OrderService */
    private $order_service;

    /** @var \WirecardPaymentGateway */
    private $module;

    /** @var BackendService */
    private $backend_service;

    /**
     * SuccessPaymentProcessing constructor.
     *
     * @param \Order $order
     * @param SuccessResponse $notification
     * @since 2.1.0
     */
    public function __construct($order, $notification)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->order_service = new OrderService($order);
        $this->module = \Module::getInstanceByName('wirecardpaymentgateway');

        $payment_type = $this->notification->getPaymentMethod();
        $shop_config = new ShopConfigurationService($payment_type);
        $config = (new PaymentConfigurationFactory($shop_config))->createConfig();

        $this->backend_service = new BackendService($config, new WirecardLogger());
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        if (!$this->isIgnorable($this->notification)) {
            $order_state = $this->getPrestaShopOrderState();
            $this->order->setCurrentState($order_state);
            $this->order->save();

            $amount = $this->notification->getRequestedAmount();
            $this->order_service->updateOrderPayment(
                $this->notification->getTransactionId(),
                _PS_OS_PAYMENT_ === $order_state ? $amount->getValue() : 0
            );

            Transaction::create(
                $this->order->id,
                $this->order->id_cart,
                $amount->getValue(),
                $amount->getCurrency(),
                $this->notification,
                $this->getTransactionState(),
                $this->order->reference
            );
        }
    }

    /**
     * Ignore all 'check-payer-response' transaction types and masterpass 'debit' and 'authorization' notifications
     *
     * @param SuccessResponse $notification
     * @return boolean
     * @since 2.1.0
     */
    private function isIgnorable($notification)
    {
        return $notification->getTransactionType() === 'check-payer-response' ||
               $this->isMasterpassIgnorable($notification);
    }

    /**
     * @param SuccessResponse $notification
     * @return boolean
     * @since 2.1.0
     */
    private function isMasterpassIgnorable($notification)
    {
        return $notification->getPaymentMethod() === 'masterpass' &&
               ($notification->getTransactionType() === 'debit' ||
               $notification->getTransactionType() === 'authorization');
    }

    /**
     * @return int
     * @throws \Exception
     * @since 2.1.0
     */
    private function getPrestaShopOrderState()
    {
        $order_state = $this->getOrderState();
        return $this->orderStateToPrestaShopOrderState($order_state);
    }

    /**
     * @return string
     * @since 2.1.0
     */
    private function getOrderState()
    {
        return $this->backend_service->getOrderState($this->notification->getTransactionType());
    }

    /**
     * @param string $order_state
     * @return mixed
     * @throws \Exception
     * @since 2.1.0
     */
    private function orderStateToPrestaShopOrderState($order_state)
    {
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
     * @return string
     * @since 2.1.0
     */
    private function getTransactionState()
    {
        if ($this->backend_service->isFinal($this->notification->getTransactionType())) {
            return 'close';
        }
        return 'open';
    }
}
