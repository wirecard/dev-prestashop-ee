<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Finder;

use WirecardEE\Prestashop\Models\Transaction;


/**
 * Class TransactionFinder
 * @package WirecardEE\Prestashop\Classes\Finder
 */
class TransactionFinder extends DbFinder
{
    /**
     * @param $orderId
     * @return Transaction|null
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getCurrentTransactionByOrderId($orderId)
    {
        $transaction = null;
        $queryBuilder = $this->getQueryBuilder();
        $query = $queryBuilder->from('wirecard_payment_gateway_tx')
            ->select('ps_wirecard_payment_gateway_tx.tx_id')
            ->leftJoin('orders', 'o', 'ps_wirecard_payment_gateway_tx.`order_id` = o.`id_order`')
            ->leftJoin('order_history', 'order_history', 'ps_wirecard_payment_gateway_tx.`order_id` = order_history.`id_order`')
            ->where('ps_wirecard_payment_gateway_tx.`order_id` = ' . pSQL($orderId) . " AND order_history.`id_order_state` = o.`current_state`");
        if ($result = $this->getDb()->getRow($query)) {
            $transaction = new Transaction(intval($result['tx_id']));
        }

        return $transaction;
    }

    /**
     * @param $transaction_id
     * @return Transaction
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getTransactionById($transaction_id)
    {
        return new Transaction($transaction_id);
    }
}