<?php

use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use WirecardEE\Prestashop\Helper\CartHelper;

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
class CartHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Cart $cart
     */
    private $cart;
    /**
     * @var CartHelper $cartHelper
     */
    private $cartHelper;

    public function setUp()
    {
        $this->cart = new \Cart(123);
        $this->cartHelper = new CartHelper($this->cart);
    }

    public function testIsReorderedItemsTrue()
    {
        $actual = $this->cartHelper->isReorderedItems();
        $this->assertEquals(RiskInfoReorder::REORDERED, $actual);
    }

    public function testIsReorderedItemsFalse()
    {
        $cart = new Cart();
        $cart->setProducts([
                0 => [
                    'id_product'        => 3,
                    'cart_quantity'     => 1,
                    'total_wt'          => 2,
                    'name'              => 'Product 3',
                    'total'             => 100,
                    'description_short' => 'short desc',
                    'reference'         => 'reference'
                ]
            ]);
        $this->cartHelper->setCart($cart);
        $actual = $this->cartHelper->isReorderedItems();
        $this->assertEquals(RiskInfoReorder::FIRST_TIME_ORDERED, $actual);
    }

    public function testCheckAvailabilityAvailableNow()
    {
        $actual = $this->cartHelper->checkAvailability();
        $this->assertEquals(RiskInfoAvailability::MERCHANDISE_AVAILABLE, $actual);
    }

    public function testCheckAvailabilityAvailableInFuture()
    {
        $cart = new Cart();
        $cart->setProducts([
                0 => [
                    'id_product'        => 3,
                    'cart_quantity'     => 2,
                    'total_wt'          => 2,
                    'name'              => 'Product 3',
                    'total'             => 100,
                    'description_short' => 'short desc',
                    'reference'         => 'reference'
                ]
            ]);
        $this->cartHelper->setCart($cart);
        $actual = $this->cartHelper->checkAvailability();
        $this->assertEquals(RiskInfoAvailability::FUTURE_AVAILABILITY, $actual);
    }
}
