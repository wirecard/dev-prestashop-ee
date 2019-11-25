<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
}
