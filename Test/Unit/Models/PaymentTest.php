<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
}
