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
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

/**
 * Class Success
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
abstract class Success implements ProcessablePaymentNotification
{
    /** @var \Order */
    protected $order;

    /** @var SuccessResponse */
    protected $notification;

    /** @var OrderService */
    protected $order_service;

    /** @var OrderManager */
    protected $order_manager;

    /** @var WirecardLogger  */
    private $logger;

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
        $this->logger = new WirecardLogger();
    }

    /**
     * @throws \Exception
     * @since 2.1.0
     */
    public function process()
    {
        $dbManager = new DBTransactionManager();
        //Acquire lock out of the try-catch block to prevent release on locking fail
        $dbManager->acquireLock($this->notification->getTransactionId(), 30);

        try {
            $this->beforeProcess();
            Transaction::create(
                $this->order->id,
                $this->order->id_cart,
                $this->notification->getRequestedAmount(),
                $this->notification,
                $this->order_manager->getTransactionState($this->notification),
                $this->order->reference
            );
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error in class:'. __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $exception->getMessage()
            );
            throw $exception;
        } finally {
            $dbManager->releaseLock($this->notification->getTransactionId());
        }
        $this->afterProcess();
    }

    /**
     * @return SettleableTransaction
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function getParentTransaction()
    {
        if ($this->notification->getTransactionType() != \Wirecard\PaymentSdk\Transaction\Transaction::TYPE_PURCHASE) {
            $transaction = Transaction::getInitialTransactionForOrder($this->order->reference);
            if ($transaction) {
                return $transaction;
            }
        }

        return new InitialTransaction($this->notification->getRequestedAmount()->getValue());
    }

    /**
     * @since 2.10.0
     */
    protected function beforeProcess()
    {
    }

    /**
     * @since 2.10.0
     */
    protected function afterProcess()
    {
    }
}
