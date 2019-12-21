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
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Models\InitialTransaction;
use WirecardEE\Prestashop\Models\SettleableTransaction;
use WirecardEE\Prestashop\Models\Transaction;

/**
 * Class Success
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
abstract class Success implements ProcessablePaymentNotification
{

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
                $amount = $this->notification->getRequestedAmount();
                Transaction::create(
                    $this->order->id,
                    $this->order->id_cart,
                    $amount,
                    $this->notification,
                    $this->order_manager->getTransactionState($this->notification),
                    $this->order->reference
                );

                $parentTransaction = $this->getParentTransaction();
                $parentTransaction->markSettledAsClosed();
                $parentTransaction->updateOrder(
                    $this->order,
                    $this->notification,
                    $this->order_manager,
                    $this->order_service
                );
            }
        } catch (\Exception $e) {
            error_log("\t\t\t" . __METHOD__ . ' ' . __LINE__ . ' ' . "exception: " . $e->getMessage());
            throw $e;
        } finally {
            $dbManager->releaseLock($this->notification->getTransactionId());
        }
    }

    /**
     * @return SettleableTransaction
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getParentTransaction()
    {
        $transaction = Transaction::getInitialTransactionForOrder($this->order->reference);

        if ($transaction) {
            return $transaction;
        }

        return new InitialTransaction($this->notification->getRequestedAmount()->getValue());
    }
}
