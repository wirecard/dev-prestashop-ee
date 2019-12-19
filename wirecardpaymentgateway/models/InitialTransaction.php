<?php


namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Response\SuccessResponse as SuccessResponse;
use WirecardEE\Prestashop\Helper\OrderManager;

class InitialTransaction implements SettleableTransaction
{

    /**
     * @var float
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
        return $this->amount;
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
     * @return bool
     * @throws \PrestaShopException
     */
    public function updateOrder(\Order $order, SuccessResponse $notification, OrderManager $orderManager)
    {
        $order_state = $orderManager->orderStateToPrestaShopOrderState($notification, false);
        if ($order_state) {
            $order->setCurrentState($order_state);
            $order->save();
            return true;
        }
        return false;
    }
}
