<?php

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use WirecardEE\Prestashop\Helper\AdditionalInformation;

class AdditionalInformationTest extends \PHPUnit_Framework_TestCase
{
    private $cart;
    private $additional;

    public function setUp()
    {
        $this->cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
        $this->additional = new AdditionalInformation();
    }

    public function createCart()
    {
        $products = array(
            array(
                'cart_quantity' => 2,
                'name'  => 'Test1',
                'total_wt' => 64.78,
                'total' => 53.98,
                'description_short' => 'Testproduct',
                'reference' => '003'
            )
        );
        $this->cart->method('getProducts')->willReturn($products);
        $this->cart->method('getTotalShippingCost')->willReturn(0);

        $taxAmount = 64.78 - 53.98;
        $taxRate = number_format($taxAmount / 64.78 * 100, 2);

        $expected = new Basket();
        $item = new Item('Test1', new Amount(64.78/2, 'EUR'), 2);
        $item->setDescription('Testproduct');
        $item->setArticleNumber('003');
        $item->setTaxRate($taxRate);
        $expected->add($item);

        return $expected;
    }


    public function testBasketWithoutShipping()
    {
        $transaction = new PayPalTransaction();

        $expected = $this->createCart();
        $expected->setVersion($transaction);

        $actual = $this->additional->createBasket($this->cart, $transaction, 'EUR');

        $this->assertEquals($expected, $actual);
    }

    public function testBasketWithShipping()
    {
        $transaction = new PayPalTransaction();

        $products = array(
            array(
                'cart_quantity' => 2,
                'name' => 'Test1',
                'total_wt' => 64.78,
                'total' => 53.98,
                'description_short' => 'Testproduct',
                'reference' => '003'
            )
        );
        $this->cart->method('getProducts')->willReturn($products);
        $this->cart->method('getTotalShippingCost')->willReturn(5.00);
        $actual = $this->additional->createBasket($this->cart, $transaction, 'EUR');

        $taxAmount = 64.78 - 53.98;
        $taxRate = number_format($taxAmount / 64.78 * 100, 2);

        $expected = new Basket();
        $expected->setVersion($transaction);
        $item = new Item('Test1', new Amount(64.78 / 2, 'EUR'), 2);
        $item->setDescription('Testproduct');
        $item->setArticleNumber('003');
        $item->setTaxRate($taxRate);
        $expected->add($item);
        $item = new Item('Shipping', new Amount(5.00, 'EUR'), 1);
        $item->setDescription('Shipping');
        $item->setArticleNumber('Shipping');
        $item->setTaxRate(0);
        $expected->add($item);

        $this->assertEquals($expected, $actual);
    }

    /*public function testBasketWithRoundingIssue()
    {
        $transaction = new PayPalTransaction();

        $products = array(
            array(
                'cart_quantity' => 3,
                'name'  => 'Test2',
                'total_wt' => 93.56,
                'total' => 77.97,
                'description_short' => 'Testproduct 2',
                'reference' => '004'
            )
        );
        $this->cart->method('getProducts')->willReturn($products);
        $this->cart->method('getTotalShippingCost')->willReturn(0);
        $additionalInformation = new AdditionalInformation();
        $actual = $additionalInformation->createBasket($this->cart, $transaction, 'EUR');

        $taxAmount = 93.56 - 77.97;
        $taxRate = number_format($taxAmount / 93.56 * 100, 2);

        $expected = new Basket();
        $expected->setVersion($transaction);
        $item = new Item('Test2 x3', new Amount(93.56, 'EUR'), 1);
        $item->setDescription('Testproduct 2');
        $item->setArticleNumber('004');
        $item->setTaxRate($taxRate);
        $expected->add($item);

        $this->assertEquals($expected, $actual);
    }*/

    public function testDescriptor()
    {
        $actual = $this->additional->createDescriptor('456');

        $this->assertContains('456', $actual);
    }

    public function testAdditionalInformation()
    {
        $expected = new PayPalTransaction();
        $basket = $this->createCart();
        $this->cart->id_customer = '1';
        $actual = $this->additional->createAdditionalInformation($this->cart, '123', $expected, 'EUR');

        $basket->setVersion($expected);
        $expected->setBasket($basket);
        $expected->setDescriptor('123');
        $expected->setAccountHolder(new \Wirecard\PaymentSdk\Entity\AccountHolder());
        $expected->setShipping(new \Wirecard\PaymentSdk\Entity\AccountHolder());
        $expected->setOrderNumber('123');
        $expected->setIpAddress('Test');
        $expected->setConsumerId('1');
    }
}
