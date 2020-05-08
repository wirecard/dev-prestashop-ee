<?php


namespace WirecardEE\Prestashop\Classes\Service;

use WirecardEE\Prestashop\Classes\Finder\TransactionFinder;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use Wirecard\PaymentSdk\Transaction\Transaction as TransactionTypes;

/**
 * Class OrderAmountCalculatorService
 * @package WirecardEE\Prestashop\Classes\Service
 */
class OrderAmountCalculatorService implements ServiceInterface
{
    /** @var array */
    const REFUND_TYPES = [
        TransactionTypes::TYPE_CREDIT,
        TransactionTypes::TYPE_REFUND_DEBIT,
        TransactionTypes::TYPE_REFUND_PURCHASE,
        TransactionTypes::TYPE_VOID_PURCHASE,
    ];

    /**
     * @var \Order
     */
    private $order;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var TransactionFinder
     */
    private $transactionFinder;

    /**
     * OrderAmountCalculatorService constructor.
     * @param \Order $order
     */
    public function __construct(\Order $order)
    {
        $this->orderService = new OrderService($order);
        $this->order = $order;
        $this->transactionFinder = new TransactionFinder();
    }


    /**
     * @return float|int
     */
    public function getOrderTotalAmount()
    {
        return $this->orderService->getOrderCart()->getOrderTotal() - $this->getRefundedAmount();
    }


    /**
     * @return float|int
     */
    public function getRefundedAmount()
    {
        $amount = 0;
        $transactionList = $this->transactionFinder->getTransactionListByOrder($this->order->id);
        foreach ($transactionList as $transaction) {
            if (in_array($transaction->getTransactionType(), self::REFUND_TYPES)) {
                $amount += $transaction->getAmount();
            }
        }
        $logger = new Logger();
        $logger->debug("Actual order total: {$this->orderService->getOrderCart()->getOrderTotal()}");
        $logger->debug("Actual refunded amount: {$amount}");
        return $amount;
    }

    // todo: in authorization US
    public function getCapturedAmount()
    {
    }
}
