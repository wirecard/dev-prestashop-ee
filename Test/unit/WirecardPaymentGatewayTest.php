<?php
require_once __DIR__ . '/../../wirecardpaymentgateway/wirecardpaymentgateway.php';

class WirecardPaymentGatewayTest extends PHPUnit_Framework_TestCase
{
    private $gateway;

    public function setUp()
    {
        $this->gateway = new WirecardPaymentGateway();
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
}