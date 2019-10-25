<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Adapter\Cart;

use WirecardEE\Prestashop\Classes\Transaction\Adapter\Product\CartItemCollection;

/**
 * Interface CartDataInterface
 * @package WirecardEE\Prestashop\Classes\Transaction\Adapter\Cart
 * @since 2.4.0
 */
interface CartDataInterface
{
    /**
     * @return CartItemCollection
     * @since 2.4.0
     */
    public function getCartItems();
}
