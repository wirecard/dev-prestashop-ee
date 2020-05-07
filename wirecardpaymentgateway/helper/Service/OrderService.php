<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Service;

use Db;

/**
 * Class OrderService
 * @package WirecardEE\Prestashop\Helper\Service
 * @since 2.1.0
 */
class OrderService
{
	const TRANSACTION_TYPE_PURCHASE = "purchase";
	const TRANSACTION_TYPE_DEBIT = "debit";
	const TRANSACTION_TYPE_CAPTURE_AUTH = "capture-authroization";

    /** @var \Order */
    private $order;

    /**
     * OrderService constructor.
     *
     * @param \Order $order
     * @since 2.1.0
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * @param $transaction
     *
     * @return bool
     * @since 2.10.0
     */
	public function createOrderPayment($transaction)
    {
        $transactionId = $transaction->transaction_id;
        $transactionType = $transaction->getTransactionType();
        if(($transactionType === self::TRANSACTION_TYPE_PURCHASE)||($transactionType===self::TRANSACTION_TYPE_DEBIT)){
            $amount = -1 * $this->order->total_paid;
            return $this->order->addOrderPayment($amount, null, $transactionId);
        }
        elseif ($transactionType === self::TRANSACTION_TYPE_CAPTURE_AUTH) {
            $amount = -1 * $this->order->total_paid;
            return $this->order->addOrderPayment($amount, null, $transactionId);
        }
    }

    /**
     * @param string $transaction_id
     * @param $amount
     *
     * @since 2.1.0
     */
    public function updateOrderPayment($transaction_id, $amount)
    {
        $order_payments = \OrderPayment::getByOrderReference($this->order->reference);

        $last_index = count($order_payments) - 1;

        if (!empty($order_payments)) {
            $order_payments[$last_index]->transaction_id = $transaction_id;
            $order_payments[$last_index]->save();
        }
    }

    /**
     * @param $orderReference
     *
     * @return boolean
     * @throws \PrestaShopDatabaseException
     * @since 2.10.0
     */
	public function deleteOrderPayment($orderReference) {
		return Db::getInstance()->executeS(
			'DELETE
			    FROM `' . _DB_PREFIX_ . 'order_payment`
			    WHERE `order_reference` = \'' . pSQL($orderReference) . '\''
		);
	}


    /**
     * @param string $order_state
     *
     * @return boolean
     * @since 2.1.0
     */
    public function isOrderState($order_state)
    {
        $order_state = \Configuration::get($order_state);
        return $this->order->current_state === $order_state;
    }

    /**
     * @return \Cart
     * @since 2.1.0
     */
    public function getOrderCart()
    {
        return \Cart::getCartByOrderId($this->order->id);
    }

    /**
     * @return \Cart
     * @since 2.1.0
     */
    public function getNewCartDuplicate()
    {
        $original_cart = $this->getOrderCart();
        return $original_cart->duplicate()['cart'];
    }

    /**
     * @param int $lang
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @since 2.7.0
     */
    public function getLatestOrderStatusFromHistory($lang = null)
    {
        $order = new \Order((int) $this->order->id);
        $order_history = $order->getHistory($lang);
        $order_status_latest = array_shift($order_history);
        $order_status = $order_status_latest['id_order_state'];

        return $order_status;
    }
}
