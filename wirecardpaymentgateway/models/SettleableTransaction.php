<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\OrderService;

interface SettleableTransaction
{

    /**
     * Get the remaining amount from the already processed amount to the total amount (amount).
     *
     * @return float
     */
    public function getRemainingAmount();

    /**
     * Get the total amount. regardless of whether it has been processed or not.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Update the order according to the newest processed state of the transaction.
     *
     * @param \Order $order
     * @param SuccessResponse $notification
     * @param OrderManager $orderManager
     * @param OrderService $orderService
     * @return bool
     */
    public function updateOrder(
        \Order $order,
        SuccessResponse $notification,
        OrderManager $orderManager,
        OrderService $orderService
    );
}
