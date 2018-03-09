<?php
use WirecardEE\Prestashop\Models\PaymentPaypal;

class PaymentPaypalTest extends PHPUnit_Framework_TestCase
{
    private $payment;

    private $paymentModule;

    private $config;

    public function setUp()
    {
        $this->config = array(
            'base_url',
            'http_user',
            'http_pass',
            'merchant_account_id',
            'secret'
        );
        $this->paymentModule = $this->getMockBuilder(WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payment = new PaymentPaypal();
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Payment Processing Gateway Paypal';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreatePaymentConfig()
    {
        $this->paymentModule->expects($this->at(0))->method('getConfigValue')->willReturn($this->config[0]);
        $this->paymentModule->expects($this->at(1))->method('getConfigValue')->willReturn($this->config[1]);
        $this->paymentModule->expects($this->at(2))->method('getConfigValue')->willReturn($this->config[2]);
        $this->paymentModule->expects($this->at(3))->method('getConfigValue')->willReturn($this->config[3]);
        $this->paymentModule->expects($this->at(4))->method('getConfigValue')->willReturn($this->config[4]);
        $actual = $this->payment->createPaymentConfig($this->paymentModule);

        $expected = new \Wirecard\PaymentSdk\Config\Config('base_url', 'http_user', 'http_pass');
        $expected->add(new \Wirecard\PaymentSdk\Config\PaymentMethodConfig('paypal', 'merchant_account_id', 'secret'));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction();

        $expected = 'paypal';
        $this->assertEquals($expected, $actual::NAME);
    }
}
