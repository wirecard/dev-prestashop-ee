<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
     *
     * @since 2.3.0
     */
    public function __construct(\Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @param $cart
     * @return \Cart
     *
     * @since 2.3.0
     */
    public function getCart()
    {
        return $this->cart;
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
