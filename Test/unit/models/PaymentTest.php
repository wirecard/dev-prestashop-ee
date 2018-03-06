<?php

use Wirecard\Prestashop\Models\Payment;
use Wirecard\PaymentSdk\Config\Config;

class PaymentTest extends PHPUnit_Framework_TestCase
{
    private $payment;
    private $config;

    public function setUp()
    {
        $this->payment = new Payment();
        $this->payment->context = new \Wirecard\Prestashop\Context();
        $this->config = new Config('baseUrl', 'httpUser', 'httpPass');
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
        $actual = $this->payment->createRedirectUrl('1', 'paypal', 'success');

        $expected = array('id_cart','payment_type','payment_state');

        $this->assertEquals($expected, $actual);
    }

    public function testCreateNotificationUrl()
    {
        $actual = $this->payment->createNotificationUrl();

        $expected = null;

        $this->assertEquals($expected, $actual);
    }
}
