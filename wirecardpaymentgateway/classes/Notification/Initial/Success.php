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
        if (OrderManager::isIgnorable($this->notification)) {
            return;
        }

        $order_state = $this->order_manager->orderStateToPrestaShopOrderState($this->notification);
        $this->order->setCurrentState($order_state);
        $this->order->save();

        $amount = $this->getRestAmount($order_state);
        $this->order_service->updateOrderPayment(
            $this->notification->getTransactionId(),
            $amount
        );

        parent::process();
    }

    /**
     * @param string $order_state
     *
     * @return float|int
     * @since 2.7.0
     */
    private function getRestAmount($order_state)
    {
        $rest_amount = 0;
        $payment_processing_state = \Configuration::get('PS_OS_PAYMENT');
        $requested_amount = $this->notification->getRequestedAmount();

        if ($payment_processing_state === $order_state) {
            $rest_amount = $requested_amount->getValue();
        }

        return $rest_amount;
    }
}
