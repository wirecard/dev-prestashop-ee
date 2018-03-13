<?php
use WirecardEE\Prestashop\Models\PaymentCreditCard;

class PaymentCreditCardTest extends PHPUnit_Framework_TestCase
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
            'three_d_merchant_account_id',
            'three_d_merchant_account_id',
            'three_d_secret',
            50,
            50,
            150,
            150
        );
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payment = new PaymentCreditCard();
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Payment Processing Gateway Credit Card';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreatePaymentConfig()
    {
        for ($i = 0; $i <= 11; $i++) {
            $this->paymentModule->expects($this->at($i))->method('getConfigValue')->willReturn($this->config[$i]);
        }
        $actual = $this->payment->createPaymentConfig($this->paymentModule);

        $expected = new \Wirecard\PaymentSdk\Config\Config('base_url', 'http_user', 'http_pass');
        $expectedPaymentConfig = new \Wirecard\PaymentSdk\Config\CreditCardConfig( 'merchant_account_id', 'secret');
        $expectedPaymentConfig->setThreeDCredentials('three_d_merchant_account_id', 'three_d_secret');
        $expectedPaymentConfig->addSslMaxLimit(new \Wirecard\PaymentSdk\Entity\Amount(50, 'EUR'));
        $expectedPaymentConfig->addThreeDMinLimit(new \Wirecard\PaymentSdk\Entity\Amount(150, 'EUR'));
        $expected->add($expectedPaymentConfig);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction();

        $expected = 'creditcard';
        $this->assertEquals($expected, $actual::NAME);
    }
}
