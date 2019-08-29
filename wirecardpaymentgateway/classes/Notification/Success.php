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

use Wirecard\PaymentSdk\Response\SuccessResponse;

use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\OrderManager;
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

    /** @var OrderManager */
    private $order_manager;

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
        $this->order_manager = new OrderManager();
    }

    /**
     * @throws \Exception
     * @since 2.1.0
     */
    public function process()
    {
        if (!OrderManager::isIgnorable($this->notification)) {
            $order_state = $this->order_manager->orderStateToPrestaShopOrderState($this->notification);
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
                $this->order_manager->getTransactionState($this->notification),
                $this->order->reference
            );
        }
    }
}
