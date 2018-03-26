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

use WirecardEE\Prestashop\Models\PaymentSofort;

class PaymentSofortTest extends PHPUnit_Framework_TestCase
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
            'secret'
        );
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payment = new PaymentSofort();

        $this->transactionData = new stdClass();
        $this->transactionData->transaction_id = 'my_secret_id';
        $this->transactionData->order_id = 'my_secret_order_id';
        $this->transactionData->cart_id = 20;
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Payment Processing Gateway Pay now.';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreatePaymentConfig()
    {
        for ($i = 0; $i <= 4; $i++) {
            $this->paymentModule->expects($this->at($i))->method('getConfigValue')->willReturn($this->config[$i]);
        }
        $actual = $this->payment->createPaymentConfig($this->paymentModule);

        $expected = new \Wirecard\PaymentSdk\Config\Config('base_url', 'http_user', 'http_pass');
        $expectedPaymentConfig = new \Wirecard\PaymentSdk\Config\PaymentMethodConfig(
            'sofortbanking',
            'merchant_account_id',
            'secret'
        );
        $expected->add($expectedPaymentConfig);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction();

        $expected = 'sofortbanking';
        $this->assertEquals($expected, $actual::NAME);
    }


    public function testCreateRefundTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\SepaTransaction();
        $accountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
        $accountHolder->setAddress(new \Wirecard\PaymentSdk\Entity\Address(null, null, null));
        $actual->setAccountHolder($accountHolder);
        $actual->setParentTransactionId('my_secret_id');
        $actual->setMandate(new \Wirecard\PaymentSdk\Entity\Mandate(
            '-my_secret_order_id-'.
            strtotime(date('Y-m-d H:i:s'))
        ));

        $this->assertEquals($actual, $this->payment->createRefundTransaction(
            $this->transactionData,
            $this->paymentModule
        ));
    }
}
