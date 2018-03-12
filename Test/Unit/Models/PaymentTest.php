<?php

require_once __DIR__ . '/../../../wirecardpaymentgateway/vendor/autoload.php';

use WirecardEE\Prestashop\Models\Payment;
use Wirecard\PaymentSdk\Config\Config;
use WirecardEE\Prestashop\Models\PaymentPaypal;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    private $payment;
    private $config;
    private $paypalPayment;

    public function setUp()
    {
        $this->payment = new Payment();
        $this->payment->context = new \Context();
        $this->config = new Config('baseUrl', 'httpUser', 'httpPass');
        $this->paypalPayment = new PaymentPaypal();
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Payment Processing Gateway';

        $this->assertEquals($expected, $actual);
    }

    public function testConfig()
    {
        $this->payment->createConfig('baseUrl', 'httpUser', 'httpPass');
        $actual = $this->payment->getConfig();

        $expected = $this->config;

        $this->assertEquals($expected, $actual);
    }

    public function testTransactionTypes()
    {
        $actual = $this->payment->getTransactionTypes();

        $expected =  array('authorization','capture');

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();

        $expected = null;

        $this->assertEquals($expected, $actual);
    }

    public function testType()
    {
        $actual = $this->paypalPayment->getType();

        $expected = 'paypal';

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransactionIsNull()
    {
        $actual = $this->payment->createTransaction();

        $this->assertNull($actual);
    }
}
