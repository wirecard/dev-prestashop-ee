<?php
require_once __DIR__ . '/../../../wirecardpaymentgateway/models/Payment.php';

use Wirecard\PaymentSdk\Config\Config;

class PaymentTest extends PHPUnit_Framework_TestCase
{
    private $payment;
    private $config;

    public function setUp()
    {
        $this->payment = new Payment();
        $this->config = new Wirecard\PaymentSdk\Config\Config('baseUrl', 'httpUser', 'httpPass');
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

    public function testCreateRedirectUrl()
    {
        $actual = $this->payment->createRedirectUrl(null);

        $expected = null;

        $this->assertEquals($expected, $actual);
    }

    public function testCreateNotificationUrl()
    {
        $actual = $this->payment->createNotificationUrl();

        $expected = null;

        $this->assertEquals($expected, $actual);
    }
}
