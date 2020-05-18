<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Service;

use \Db;

/**
 * Class OrderService
 * @package WirecardEE\Prestashop\Helper\Service
 * @since 2.1.0
 */
class OrderService
{
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
     * @param string $transactionId
     * @param float $amount
     *
     * @return bool
     * @since 2.10.0
     */
    public function createOrderPayment($transactionId, $amount)
    {
        $flownAmount = $this->flownAmount($amount);
        if ($flownAmount) {
            return $this->order->addOrderPayment((float)$flownAmount, null, $transactionId);
        }
    }

    /**
     * @param float $amount
     *
     * @return float|null
     * @since 2.10.0
     */
    public function flownAmount($amount)
    {
        $orderState = $this->order->current_state;
        switch ($orderState) {
            case \Configuration::get('PS_OS_REFUND'):
            case \Configuration::get('WIRECARD_OS_PARTIAL_REFUNDED'):
                return $amount * -1;
            case \Configuration::get('WIRECARD_OS_PARTIAL_CAPTURED'):
                return $amount;
            default:
                return null;
        }
    }

    /**
     * @param string $transaction_id
     *
     * @since 2.1.0
     */
    public function addTransactionIdToOrderPayment($transaction_id)
    {
        $order_payments = \OrderPayment::getByOrderReference($this->order->reference);
        $last_index = count($order_payments) - 1;
        $order_current_state = (int) $this->order->getCurrentState();
        $order_payment_state = (int) \Configuration::get('PS_OS_PAYMENT');

        if (!empty($order_payments)&&($order_current_state === $order_payment_state)) {
            $order_payments[$last_index]->transaction_id = $transaction_id;
            $order_payments[$last_index]->save();
        }
        //todo: $amount will be used in the partial operations
    }


    /**
     * @param string $orderReference
     *
     * @return boolean
     * @throws \PrestaShopDatabaseException
     * @since 2.10.0
     */
    public function deleteOrderPayment($orderReference)
    {
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
