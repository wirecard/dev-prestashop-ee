<?php


namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Response\SuccessResponse as SuccessResponse;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\OrderService;

class InitialTransaction implements SettleableTransaction
{

    /**
     * @var float The total amount of the transaction.
     */
    private $amount;

    /**
     * InitialTransaction constructor.
     * @param $amount
     */
    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getProcessedAmount()
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getRemainingAmount()
    {
        return $this->getAmount();
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return bool
     */
    public function markSettledAsClosed()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isSettled()
    {
        return false;
    }

    /**
     * @param \Order $order
     * @param SuccessResponse $notification
     * @param OrderManager $orderManager
     * @param OrderService $orderService
     * @return bool
     * @throws \PrestaShopException
     */
    public function updateOrder(\Order $order, SuccessResponse $notification, OrderManager $orderManager, OrderService $orderService)
    {
        $order_state = $orderManager->orderStateToPrestaShopOrderState($notification);
        error_log("\t\t\t" . __METHOD__ . ' ' . __LINE__ . ' ' . json_encode(compact('order_state')));
        if ($order_state) {
            $order->setCurrentState($order_state);
            $order->save();
            return true;
        }
        return false;
    }
}
