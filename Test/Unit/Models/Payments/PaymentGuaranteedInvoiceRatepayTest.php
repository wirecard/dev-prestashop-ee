<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

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

    public function testGetPostProcessingMandatoryEntities()
    {
        $expected = ['basket'];

        $this->assertEquals(
            $expected,
            $this->payment->getPostProcessingMandatoryEntities()
        );
    }
}
