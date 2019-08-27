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

use WirecardEE\Prestashop\Models\Payment;
use Wirecard\PaymentSdk\Config\Config;
use WirecardEE\Prestashop\Models\PaymentPaypal;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /** @var PaymentPaypal */
    private $payment;
    private $config;

    /** @var WirecardPaymentGateway */
    private $paymentModule;

    public function setUp()
    {
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = new PaymentPaypal();
        $this->payment->context = new \Context();
        $this->config = new Config('baseUrl', 'httpUser', 'httpPass');
        $this->config->setShopInfo(EXPECTED_SHOP_NAME, _PS_VERSION_);
        $this->config->setPluginInfo(EXPECTED_PLUGIN_NAME, \WirecardPaymentGateway::VERSION);
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard PayPal';

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

        $this->assertEquals('PayPal', $actual['tab']);
        $this->assertCount(12, $actual['fields']);
    }

    public function testType()
    {
        $actual = $this->payment->getType();

        $expected = 'paypal';

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransactionIsNull()
    {
        $actual = $this->payment->createTransaction($this->paymentModule, new Cart(), array(), 'ADB123');

        $this->assertInstanceOf(\Wirecard\PaymentSdk\Transaction\PayPalTransaction::class, $actual);
    }

    public function testCanCancel()
    {
        $this->assertEquals(false, $this->payment->canCancel('test'));
    }

    public function testCanCapture()
    {
        $this->assertEquals(false, $this->payment->canCapture('test'));
    }

    public function testCanRefund()
    {
        $this->assertEquals(true, $this->payment->canRefund('capture-authorization'));
    }
}
