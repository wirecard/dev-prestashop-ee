<?php


namespace WirecardEE\Prestashop\Classes\Service;

use WirecardEE\Prestashop\Classes\Finder\TransactionFinder;
use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\NumericHelper;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use Wirecard\PaymentSdk\Transaction\Transaction as TransactionTypes;

/**
 * Class OrderAmountCalculatorService
 * @package WirecardEE\Prestashop\Classes\Service
 */
class OrderAmountCalculatorService implements ServiceInterface
{
    use NumericHelper;

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
     * @todo: delete in US 6245
     */
    public function getOrderOpenAmount()
    {
        return $this->getOrderTotalAmount() - $this->getOrderRefundedAmount();
    }

    /**
     * @return float|int
     */
    public function getOrderTotalAmount()
    {
        return $this->orderService->getOrderCart()->getOrderTotal();
    }

    /**
     * @return float|int
     */
    public function getOrderRefundedAmount()
    {
        $transactionList = $this->transactionFinder->getTransactionListByOrder($this->order->id);
        return $this->sumRefundedTransactions($transactionList);
    }

    /**
     * @param array|\WirecardEE\Prestashop\Models\Transaction[] $transactionList
     * @return float
     */
    private function sumRefundedTransactions($transactionList)
    {
        $amount = 0.0;
        foreach ($transactionList as $transaction) {
            if (in_array($transaction->getTransactionType(), self::REFUND_TYPES)) {
                $amount += (float)$transaction->getAmount();
            }
        }
        return $amount;
    }

    /**
     * @param string $transactionId
     */
    public function markParentAsClosedOnFullAmount($transactionId)
    {
        $transactionManager = new DBTransactionManager();
        $parentTransaction = $this->transactionFinder->getTransactionById($transactionId);
        if (!is_null($parentTransaction)) {
            $transactionList = $this->transactionFinder->getAllChildrenByParentTransaction(
                $transactionId
            );
            $refundedAmount = $this->sumRefundedTransactions($transactionList);

            // Amounts from Transaction model always strings (!!!) ...
            if ($this->equals(
                (float)$parentTransaction->getAmount(),
                (float)$refundedAmount
            )) {
                $transactionManager->markTransactionClosed($transactionId);
            }
        }
    }
}
