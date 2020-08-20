<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

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
     * @param \Order $order
     * @param SuccessResponse $notification
     * @param OrderManager $orderManager
     * @param OrderService $orderService
     * @return bool
     * @throws \PrestaShopException
     */
    public function updateOrder(
        \Order $order,
        SuccessResponse $notification,
        OrderManager $orderManager,
        OrderService $orderService
    ) {
        $orderState = $orderManager->orderStateToPrestaShopOrderState($notification);
        if ($orderState) {
            $order->setCurrentState($orderState);
            $order->save();
            return true;
        }
        return false;
    }
}
