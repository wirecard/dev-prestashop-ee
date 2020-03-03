<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification\Initial;

use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;
use WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;
use WirecardEE\Prestashop\Helper\OrderManager;

class Success extends AbstractSuccess implements ProcessablePaymentNotification
{
    public function process()
    {
        $order_state = $this->order_manager->orderStateToPrestaShopOrderState($this->notification);
        $this->order->setCurrentState($order_state);
        $this->order->save();

        $amount = $this->notification->getRequestedAmount();
        $has_amount = _PS_OS_PAYMENT_ === $order_state ? $amount->getValue() : 0;

        $this->order_service->updateOrderPayment(
            $this->notification->getTransactionId(),
            $has_amount
        );

        if (OrderManager::isIgnorable($this->notification)) {
            return;
        }
        parent::process();
    }
}
