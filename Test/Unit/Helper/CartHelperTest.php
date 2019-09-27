<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use WirecardEE\Prestashop\Helper\CartHelper;

class CartHelperTest extends \PHPUnit_Framework_TestCase
{

    private function newCartHelper($id)
    {
        $cart = new \Cart($id);
        return new CartHelper($cart);
    }

    public function testIsReorderedItemsTrue()
    {
        $actual = $this->newCartHelper(123)->isReorderedItems();
        $this->assertEquals(RiskInfoReorder::REORDERED, $actual);
    }

    public function testIsReorderedItemsFalse()
    {
        $cartHelper = $this->newCartHelper(123);
        $cartHelper->getCart()->id = null;

        $cartHelper->getCart()->setProducts([
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
        $actual = $cartHelper->isReorderedItems();
        $this->assertEquals(RiskInfoReorder::FIRST_TIME_ORDERED, $actual);
    }

    public function testCheckAvailabilityAvailableNow()
    {
        $actual = $this->newCartHelper(123)->checkAvailability();
        $this->assertEquals(RiskInfoAvailability::MERCHANDISE_AVAILABLE, $actual);
    }

    public function testCheckAvailabilityAvailableInFuture()
    {
        $cartHelper = $this->newCartHelper(122);
        $cartHelper->getCart()->setProducts([
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

        $actual = $cartHelper->checkAvailability();
        $this->assertEquals(RiskInfoAvailability::FUTURE_AVAILABILITY, $actual);
    }
}
