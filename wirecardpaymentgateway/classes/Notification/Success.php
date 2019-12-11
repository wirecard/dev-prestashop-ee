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

use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\NumericHelper;
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

    use NumericHelper;

    /** @var \Order */
    private $order;

    /** @var SuccessResponse */
    private $notification;

    /** @var OrderService */
    private $order_service;

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
        $this->order_manager = new OrderManager();
    }

    /**
     * @throws \Exception
     * @since 2.1.0
     */
    public function process()
    {
        $dbManager = new DBTransactionManager();
        //outside of the try block. If locking fails, we don't want to attempt to release it
        $dbManager->acquireLock($this->notification->getTransactionId(), 30);
        try {
            if (!OrderManager::isIgnorable($this->notification)) {
                $order_state = $this->order_manager->orderStateToPrestaShopOrderState($this->notification);
                $this->order->setCurrentState($order_state);
                $this->order->save();

                $amount = $this->notification->getRequestedAmount();
                $this->order_service->updateOrderPayment(
                    $this->notification->getTransactionId(),
                    _PS_OS_PAYMENT_ === $order_state ? $amount->getValue() : 0
                );

                $orderManager = new OrderManager();
                $newId = Transaction::create(
                    $this->order->id,
                    $this->order->id_cart,
                    $amount,
                    $this->notification,
                    $orderManager->getTransactionState($this->notification),
                    $this->order->reference
                );

                $parentTransactionId = $this->notification->getParentTransactionId();
                $parentTransaction = new Transaction();
                $hydrated = $parentTransaction->hydrateByTransactionId($parentTransactionId);

                if ($hydrated) {
                    $parentTransactionProcessedAmount = $parentTransaction->getProcessedAmount();
                    $parentTransactionAmount = $parentTransaction->getAmount();

                    if ($this->equals($parentTransactionProcessedAmount, $parentTransactionAmount)) {
                        $transactionManager = new DBTransactionManager();
                        $transactionManager->markTransactionClosed($parentTransactionId);
                    }
                }
            }
        } finally {
            $dbManager->releaseLock($this->notification->getTransactionId());
        }
    }
}
