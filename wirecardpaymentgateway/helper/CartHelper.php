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

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;

/**
 * Class CartHelper
 * @package WirecardEE\Prestashop\Helper
 *
 * @since v2.2.0
 */
class CartHelper
{
    /**
     * @var \Cart $cart
     */
    protected $cart;

    /**
     * CartHelper constructor.
     * @param \Cart $cart
     */
    public function __construct(\Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @param $cart
     */
    public function setCart($cart){
        $this->cart = $cart;
    }

    /**
     * Check if one item in
     * @param \Cart $cart
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     *
     * @since 2.2.0
     */
    public function isReorderedItems()
    {
        // All orders from customer
        $orders = \Order::getCustomerOrders($this->cart->id_customer);
        $cartProducts = $this->cart->getProducts();
        $cartProductIds = array();
        foreach ($cartProducts as $product) {
            $cartProductIds[] = $product['id_product'];
        }
        foreach ($orders as $order) {
            $orderClass = new \Order($order['id_order']);
            $orderProducts = $orderClass->getProducts();
            foreach ($orderProducts as $orderProduct) {
                if (in_array($orderProduct['id_product'], $cartProductIds)) {
                    return RiskInfoReorder::REORDERED;
                }
            }
        }
        return RiskInfoReorder::FIRST_TIME_ORDERED;
    }

    /**
     * @param \Cart $cart
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     *
     * @since 2.2.0
     */
    public function checkAvailability()
    {
        foreach ($this->cart->getProducts() as $product) {
            $productClass = new \Product($product['id_product']);
            if (!$productClass->checkQty($product['cart_quantity'])) {
                return RiskInfoAvailability::FUTURE_AVAILABILITY;
            }
        }
        return RiskInfoAvailability::MERCHANDISE_AVAILABLE;
    }
}
