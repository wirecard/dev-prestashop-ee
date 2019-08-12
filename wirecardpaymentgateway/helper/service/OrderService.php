<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace WirecardEE\Prestashop\Helper\Service;

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
     * @param string $transaction_id
     * @param float $amount
     * @since 2.1.0
     */
    public function updateOrderPayment($transaction_id, $amount)
    {
        $order_payments = \OrderPayment::getByOrderReference($this->order->reference);

        $last_transaction_index = count($order_payments) - 1;

        if (!empty($order_payments)) {
            $order_payments[$last_transaction_index]->transaction_id = $transaction_id;
            $order_payments[$last_transaction_index]->amount = $amount;
            $order_payments[$last_transaction_index]->save();
        }
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
        if ($this->order->current_state === $order_state) {
            return true;
        }

        return false;
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
}
