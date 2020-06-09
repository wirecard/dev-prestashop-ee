<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Models\PaymentCreditCard;

class PaymentCreditCardTest extends PHPUnit_Framework_TestCase
{
    private $payment;

    private $paymentModule;

    private $transactionData;

    public function setUp()
    {
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigValue', 'createRedirectUrl', 'createNotificationUrl'])
            ->getMock();
        $this->paymentModule->version = \WirecardPaymentGateway::VERSION;

        $this->payment = new PaymentCreditCard();

        $this->transactionData = new stdClass();
        $this->transactionData->transaction_id = 'my_secret_id';
        $this->transactionData->amount = 20;
        $this->transactionData->currency = 'EUR';
        $this->transactionData->transaction_type = null;
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Credit Card';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction(
            $this->paymentModule,
            new Cart(),
            array(
              'expiration_month'=>'01',
              'expiration_year'=>'2018'
            ),
            'ADB123'
        );

        $expected = 'creditcard';
        $this->assertEquals($expected, $actual::NAME);
    }


    public function testGetPostProcessingMandatoryEntities()
    {
        $expected = [];

        $this->assertEquals(
            $expected,
            $this->payment->getPostProcessingMandatoryEntities()
        );
    }
}
