<?php


namespace WirecardEE\Prestashop\Classes\Service;

use Wirecard\PaymentSdk\Transaction\Transaction as TransactionTypes;
use WirecardEE\Prestashop\Classes\Finder\TransactionFinder;
use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\NumericHelper;
use WirecardEE\Prestashop\Helper\Service\OrderService;

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
        TransactionTypes::TYPE_REFUND_CAPTURE,
        TransactionTypes::TYPE_VOID_PURCHASE,
        TransactionTypes::TYPE_VOID_CAPTURE,
    ];

    /** @var array */
    const CAPTURE_TYPES = [
        TransactionTypes::TYPE_CAPTURE_AUTHORIZATION,
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
    public function __construct($order)
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
        return $this->orderService->getOrderCart()->getOrderTotal();
    }

    /**
     * @param null|string $forTransactionId
     * @return float|int
     */
    public function getOrderRefundedAmount($forTransactionId = null)
    {
        $orderTransactionList = $this->transactionFinder->getTransactionListByOrder(
            $this->order->id,
            $forTransactionId
        );
        return $this->sumByTransactionTypes($orderTransactionList, self::REFUND_TYPES);
    }

    /**
     * @param null|string $forTransactionId
     * @return float
     */
    public function getOrderCapturedAmount($forTransactionId = null)
    {
        $orderTransactionList = $this->transactionFinder->getTransactionListByOrder(
            $this->order->id,
            $forTransactionId
        );
        return $this->sumByTransactionTypes($orderTransactionList, self::CAPTURE_TYPES);
    }

    /**
     * @param array|\WirecardEE\Prestashop\Models\Transaction[] $orderTransactionList
     * @param array $typeList
     * @return float
     */
    private function sumByTransactionTypes($orderTransactionList, $typeList = [])
    {
        $amount = 0.0;
        foreach ($orderTransactionList as $transaction) {
            if (in_array($transaction->getTransactionType(), $typeList)) {
                $amount += (float)$transaction->getAmount();
            }
        }
        return $amount;
    }

    /**
     * @param string $transactionId
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @since 2.10.0
     */
    public function markSettledParentAsClosed($transactionId)
    {
        $transactionManager = new DBTransactionManager();
        $parentTransaction = $this->transactionFinder->getTransactionById($transactionId);
        if (!empty($parentTransaction->getTransactionId())) {
            return;
        }
        if (!is_null($parentTransaction)) {
            $isFullRefundedTransactionAmount = $this->equals(
                (float)$parentTransaction->getAmount(),
                (float)$this->getOrderRefundedAmount($parentTransaction->getTransactionId())
            );
            $isFullCapturedTransactionAmount = $this->equals(
                (float)$parentTransaction->getAmount(),
                (float)$this->getOrderCapturedAmount($parentTransaction->getTransactionId())
            );
            // Amounts from Transaction model always strings (!!!) ...
            if ($isFullRefundedTransactionAmount || $isFullCapturedTransactionAmount) {
                $transactionManager->markTransactionClosed($transactionId);
            }
        }
    }
}
