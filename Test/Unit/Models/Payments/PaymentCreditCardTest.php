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

use WirecardEE\Prestashop\Models\PaymentCreditCard;

class PaymentCreditCardTest extends PHPUnit_Framework_TestCase
{
    private $payment;

    private $paymentModule;

    private $config;

    private $transactionData;

    public function setUp()
    {
        $this->config = array(
            'base_url',
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
            50,
            150,
            150,
            150
        );

        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigValue', 'createRedirectUrl', 'createNotificationUrl'])
            ->getMock();

        $this->payment = new PaymentCreditCard($this->paymentModule);

        $this->transactionData = new stdClass();
        $this->transactionData->transaction_id = 'my_secret_id';
        $this->transactionData->amount = 20;
        $this->transactionData->currency = 'EUR';
        $this->transactionData->transaction_type = null;
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Credit Card';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreatePaymentConfig()
    {
        for ($i = 0; $i <= 13; $i++) {
            $this->paymentModule->expects($this->at($i))->method('getConfigValue')->willReturn($this->config[$i + 1]);
        }
        $actual = $this->payment->createPaymentConfig($this->paymentModule);

        $expected = new \Wirecard\PaymentSdk\Config\Config('base_url', 'http_user', 'http_pass');
        $expectedPaymentConfig = new \Wirecard\PaymentSdk\Config\CreditCardConfig('merchant_account_id', 'secret');
        $expectedPaymentConfig->setThreeDCredentials('three_d_merchant_account_id', 'three_d_secret');
        $expectedPaymentConfig->addSslMaxLimit(new \Wirecard\PaymentSdk\Entity\Amount(50, 'EUR'));
        $expectedPaymentConfig->addThreeDMinLimit(new \Wirecard\PaymentSdk\Entity\Amount(150, 'EUR'));
        $expected->add($expectedPaymentConfig);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransaction()
    {
        for ($i = 0; $i <= 11; $i++) {
            $this->paymentModule->expects($this->at($i))->method('getConfigValue')->willReturn($this->config[$i]);
        }

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

    public function testGetRequestData()
    {
        $context = new Context();

        $expected = array(
            'transaction_type' => 'authorization',
            'merchant_account_id' => 'merchant_account_id',
            'requested_amount' => 20,
            'requested_amount_currency' => 'EUR',
            'locale' => 'en',
            'payment_method' => 'creditcard',
            'attempt_three_d' => false,
            'field_name_1' => 'paysdk_orderId',
            'field_value_1' => 102,
            'ip_address' => '127.0.0.1'
        );

        $this->paymentModule->expects($this->at(0))->method('getConfigValue')->willReturn('base_url');
        $this->paymentModule->expects($this->at(1))->method('getConfigValue')->willReturn('authorization');

        for ($i = 0; $i <= 14; $i++) {
            $this->paymentModule->expects($this->at($i + 1))->method('getConfigValue')->willReturn($this->config[$i]);
        }
        $actual = (array) json_decode($this->payment->getRequestData($this->paymentModule, $context, 123));
        //unset the generated request id as it is different every time
        unset($actual['request_id'], $actual['request_signature'], $actual['request_time_stamp']);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCancelTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\CreditCardTransaction();
        $actual->setParentTransactionId('my_secret_id');
        $actual->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $this->assertEquals($actual, $this->payment->createCancelTransaction($this->transactionData));
    }

    public function testCreatePayTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\CreditCardTransaction();
        $actual->setParentTransactionId('my_secret_id');
        $actual->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $this->assertEquals($actual, $this->payment->createPayTransaction($this->transactionData));
    }
}
