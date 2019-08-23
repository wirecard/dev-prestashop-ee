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

use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;

class PaymentGuaranteedInvoiceRatepayTest extends PHPUnit_Framework_TestCase
{
    private $payment;

    private $paymentModule;

    private $config;

    private $transactionData;

    public function setUp()
    {
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentModule->version = \WirecardPaymentGateway::VERSION;

        $shopConfig = $this->createMock(ShopConfigurationService::class);

        $shopConfig->method('getField')
            ->will(
                $this->returnValueMap([
                    ['amount_min', 20],
                    ['amount_max', 350],
                    ['allowed_currencies', '["EUR"]']
                ])
            );

        $this->payment = new PaymentGuaranteedInvoiceRatepay($this->paymentModule);
        setProtectedProperty($this->payment, 'configuration', $shopConfig);

        $this->transactionData = new stdClass();
        $this->transactionData->transaction_id = 'my_secret_id';
        $this->transactionData->order_id = 'my_secret_order_id';
        $this->transactionData->cart_id = new stdClass();
        $this->transactionData->cart_id->id_customer = 11;
        $this->transactionData->cart_id->id_address_invoice = 12;
        $this->transactionData->cart_id->id_address_delivery = 13;
        $this->transactionData->amount = 25;
        $this->transactionData->currency = 'EUR';
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Guaranteed Invoice';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction(new PaymentModule(), new Cart(), array(), 'ADB123');

        $expected = 'ratepayinvoice';
        $this->assertEquals($expected, $actual::NAME);
    }

    public function testIsAvailable()
    {
        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_customer = 2;
        $cart->method('getOrderTotal')->willReturn(40);

        $actual = $this->payment->isAvailable($this->paymentModule, $cart);

        $this->assertTrue($actual);
    }

    public function testIsAvailableVirtual()
    {
        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_customer = 2;
        $cart->method('getOrderTotal')->willReturn(40);
        $cart->method('isVirtualCart')->willReturn(true);

        $actual = $this->payment->isAvailable($this->paymentModule, $cart);

        $this->assertFalse($actual);
    }

    public function testIsAvailableLimit()
    {
        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_customer = 2;
        $cart->method('getOrderTotal')->willReturn(15);

        $actual = $this->payment->isAvailable($this->paymentModule, $cart);

        $this->assertFalse($actual);
    }


    public function testCreateCancelTransaction()
    {
        $expected = new \Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction();
        $expected->setParentTransactionId('my_secret_id');
        $expected->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $actual = $this->payment->createCancelTransaction($this->transactionData, $this->paymentModule);

        $this->assertEquals($expected, $actual);
    }

    public function testCreatePayTransaction()
    {
        $expected = new \Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction();
        $expected->setParentTransactionId('my_secret_id');
        $expected->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $basket = new \Wirecard\PaymentSdk\Entity\Basket();
        $basket->setVersion($expected);
        $expected->setBasket($basket);

        $actual = $this->payment->createPayTransaction($this->transactionData);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateRefundTransaction()
    {
        $expected = new \Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction();
        $expected->setParentTransactionId('my_secret_id');
        $expected->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $basket = new \Wirecard\PaymentSdk\Entity\Basket();
        $basket->setVersion($expected);
        $expected->setBasket($basket);

        $actual = $this->payment->createRefundTransaction($this->transactionData, $this->paymentModule);

        $this->assertEquals($expected, $actual);
    }
}
