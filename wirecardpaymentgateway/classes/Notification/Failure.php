<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification;

use Wirecard\PaymentSdk\Response\FailureResponse;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class Failure
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
final class Failure implements ProcessablePaymentNotification
{
    /** @var \Order  */
    private $order;

    /** @var FailureResponse  */
    private $notification;

    /** @var OrderService */
    private $order_service;

    /**
     * FailurePaymentProcessing constructor.
     *
     * @param \Order $order
     * @param FailureResponse $notification
     * @since 2.1.0
     */
    public function __construct($order, $notification)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->order_service = new OrderService($order);
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        if ($this->order->getCurrentState() !== _PS_OS_ERROR_) {
            $this->order->setCurrentState(_PS_OS_ERROR_);
            $this->order->save();

            $this->order_service->updateOrderPayment($this->notification->getData()['transaction-id'], 0);
        }
    }
}
