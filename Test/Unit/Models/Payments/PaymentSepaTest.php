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

use WirecardEE\Prestashop\Models\PaymentSepaDirectDebit;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;

class PaymentSepaTest extends PHPUnit_Framework_TestCase
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
            'secret',
            'creditor_id'
        );
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentModule->version = \WirecardPaymentGateway::VERSION;

        $this->payment = new PaymentSepaDirectDebit($this->paymentModule);
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard SEPA Direct Debit';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreateTransaction()
    {
        $values = array(
            'sepaFirstName' => 'Max',
            'sepaLastName' => 'Mustermann',
            'sepaIban' => '123456',
            'sepaBic' => '12354'
        );
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction($this->paymentModule, new Cart(), $values, 'ADB123');

        $expected = 'sepadirectdebit';
        $this->assertEquals($expected, $actual::NAME);
    }

    public function testSepaMandate()
    {
        $expected = 'WIRECARD_PAYMENT_GATEWAY_SEPADIRECTDEBIT_CREDITOR_ID-id-' . strtotime(date('Y-m-d H:i:s'));
        $this->assertEquals($expected, $this->payment->generateMandateId($this->paymentModule, 'id'));
    }
}
