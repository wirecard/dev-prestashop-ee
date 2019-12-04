<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
                $amount,
                $this->notification,
                $this->order_manager->getTransactionState($this->notification),
                $this->order->reference
            );
        }
    }
}
