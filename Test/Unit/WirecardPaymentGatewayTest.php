<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

define('_PS_VERSION_', '9.9.9.9');

use WirecardEE\Prestashop\Models\PaymentPaypal;

require_once __DIR__ . '/../../wirecardpaymentgateway/wirecardpaymentgateway.php';

class WirecardPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WirecardPaymentGateway
     */
    private $gateway;

    public function setUp()
    {
        $this->gateway = new \WirecardPaymentGateway();
        $this->gateway->context->controller = new Controller();
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

    public function testRedirectUrl()
    {
        $actual = $this->gateway->createRedirectUrl('1', 'paypal', 'success', '102');

        $this->assertNotNull($actual);
    }

    public function testNotificationUrl()
    {
        $actual = $this->gateway->createNotificationUrl('1', 'paypal', '102');

        $this->assertNotNull($actual);
    }

    public function testPaymentOptions()
    {
        $actual = $this->gateway->hookPaymentOptions(array('cart' => new Cart()));

        $this->assertCount(8, $actual);
    }

    /*public function testHookActionFrontControllerSetMedia()
    {
        $this->assertEquals(true, $this->gateway->hookActionFrontControllerSetMedia());
    }*/

    public function testExecuteSql()
    {
        $this->assertEquals(true, $this->gateway->executeSql("UPDATE foo SET foo='bar'"));
    }
}
