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
        $actual = $this->gateway->hookPaymentOptions(array('cart' => new Cart()));

        $this->assertCount(8, $actual);
    }

    public function testHookActionFrontControllerSetMedia()
    {
        $this->assertEquals(true, $this->gateway->hookActionFrontControllerSetMedia());
    }
}
