<?php

use WirecardEE\Prestashop\Models\PaymentPaypal;

require_once __DIR__ . '/../../wirecardpaymentgateway/wirecardpaymentgateway.php';

class WirecardPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
    private $gateway;

    public function setUp()
    {
        $this->gateway = new \WirecardPaymentGateway();
    }

    public function testConfiguration()
    {
        $actual = $this->gateway->getAllConfigurationParameters();

        $this->assertTrue(!empty($actual));
    }

    public function testInstallSuccess()
    {
        $actual = $this->gateway->install();

        $this->assertTrue($actual);
    }


    public function testUninstallSuccess()
    {
        $actual = $this->gateway->uninstall();

        $this->assertTrue($actual);
    }

    public function testInstallFailure()
    {
        $this->gateway->setName('');
        $actual = $this->gateway->install();

        $this->assertFalse($actual);
    }

    public function testUninstallFailure()
    {
        $this->gateway->setName('');
        $actual = $this->gateway->uninstall();

        $this->assertFalse($actual);
    }

    public function testContent()
    {
        $actual = $this->gateway->getContent();

        $this->assertNotNull($actual);
    }

    public function testGetPaymentFromType()
    {
        $actual = $this->gateway->getPaymentFromType('paypal');

        $expected = PaymentPaypal::class;

        $this->assertInstanceOf($expected, $actual);
    }

    public function testGetPaymentFromTypeInvalid()
    {
        $actual = $this->gateway->getPaymentFromType('invalidpayment');

        $this->assertFalse($actual);
    }

    public function testGetConfigValue()
    {
        $actual = $this->gateway->getConfigValue('paypal', 'title');

        $expected = 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_TITLE';

        $this->assertEquals($expected, $actual);
    }

    public function testRedirectUrl()
    {
        $actual = $this->gateway->createRedirectUrl('1', 'paypal', 'success');

        $this->assertNotNull($actual);
    }

    public function testNotificationUrl()
    {
        $actual = $this->gateway->createNotificationUrl('1', 'paypal');

        $this->assertNotNull($actual);
    }

    public function testPaymentOptions()
    {
        $actual = $this->gateway->hookPaymentOptions('test');

        $this->assertCount(2, $actual);
    }

    public function testHookDisplayPaymentReturn()
    {
        $actual = $this->gateway->hookDisplayPaymentReturn('test');

        $this->assertContains('payment_return', $actual);
    }

    public function testHookActionFrontControllerSetMedia()
    {
       $this->assertEquals(true,  $this->gateway->hookActionFrontControllerSetMedia());
    }
}
