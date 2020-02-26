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
use \WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\OrderService;

class Success extends AbstractSuccess implements ProcessablePaymentNotification
{
	public function __construct($order, $notification)
    {
        parent::__construct($order, $notification);
        $this->order_service = new OrderService($this->order);
        $this->order_manager = new OrderManager();
    }

    public function process()
    {
        $this->order->getCurrentState();
        $order_state = $this->order_manager->orderStateToPrestaShopOrderState($this->notification);
        $this->order->setCurrentState($order_state);
        $this->order->save();

        $amount = $this->notification->getRequestedAmount();
        $this->order_service->updateOrderPayment(
        	$this->notification->getTransactionId(),
            _PS_OS_PAYMENT_ === $order_state ? $amount->getValue() : 0
        );

        try {
            if (!OrderManager::isIgnorable($this->notification)) {
                parent::process();
            }
        } catch (\Exception $e) {
            error_log("\t\t\t" . __METHOD__ . ' ' . __LINE__ . ' ' . "exception: " . $e->getMessage());
            throw $e;
        }
    }
}
