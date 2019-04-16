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

use WirecardEE\Prestashop\Models\PaymentUnionPayInternational;

class PaymentUnionPayInternationalTest extends PHPUnit_Framework_TestCase
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
            'secret'
        );
        $this->paymentModule = $this->getMockBuilder(\WirecardPaymentGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payment = new PaymentUnionPayInternational($this->paymentModule);

        $this->transactionData = new stdClass();
        $this->transactionData->transaction_id = 'my_secret_id';
        $this->transactionData->amount = 20;
        $this->transactionData->currency = 'EUR';
        $this->transactionData->cart_id = new stdClass();
        $this->transactionData->cart_id->id_customer = 11;
        $this->transactionData->cart_id->id_address_invoice = 12;
        $this->transactionData->cart_id->id_address_delivery = 13;
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard UnionPay International';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }

    public function testCreatePaymentConfig()
    {
        $this->paymentModule->expects($this->at(0))->method('getConfigValue')->willReturn($this->config[1]);
        $this->paymentModule->expects($this->at(1))->method('getConfigValue')->willReturn($this->config[2]);
        $this->paymentModule->expects($this->at(2))->method('getConfigValue')->willReturn($this->config[3]);
        $this->paymentModule->expects($this->at(3))->method('getConfigValue')->willReturn($this->config[4]);
        $this->paymentModule->expects($this->at(4))->method('getConfigValue')->willReturn($this->config[5]);
        $actual = $this->payment->createPaymentConfig($this->paymentModule);

        $expected = new \Wirecard\PaymentSdk\Config\Config(
            'base_url',
            'http_user',
            'http_pass'
        );
        $expected->add(new \Wirecard\PaymentSdk\Config\PaymentMethodConfig(
            'unionpayinternational',
            'merchant_account_id',
            'secret'
        ));

        $this->assertEquals($expected, $actual);
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction(
            $this->paymentModule,
            new Cart(),
            array('tokenId' => 'test'),
            'ADB123'
        );

        $expected = 'unionpayinternational';
        $this->assertEquals($expected, $actual::NAME);
    }

    public function testCreateCancelTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\UpiTransaction();
        $actual->setParentTransactionId('my_secret_id');
        $actual->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $this->assertEquals($actual, $this->payment->createCancelTransaction($this->transactionData));
    }

    public function testCreatePayTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\UpiTransaction();
        $actual->setParentTransactionId('my_secret_id');
        $actual->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

        $this->assertEquals($actual, $this->payment->createPayTransaction($this->transactionData));
    }


    public function testProcessCreditCard()
    {
        $products = [
            [
                'cart_quantity' => 20,
                'name' => 'Test1',
                'total_wt' => 200.00,
                'total' => 200.00,
                'description_short' => 'Testproduct',
                'reference' => '003'
            ]
        ];

        Configuration::setBasketConfig(true);
        Configuration::setAdditionalConfig(true);

        $payment = new \WirecardPaymentGatewayPaymentModuleFrontController();
        $cart = new Cart();
        $cart->setId('2');
        $cart->setOrderTotal('200');
        $cart->id_currency = '1';
        $cart->setProducts($products);
        $paymentType = 'unionpayinternational';
        $orderId = '55555';

        $config = new \Wirecard\PaymentSdk\Config\Config('base_url', 'http_user', 'http_pass');
        $expectedPaymentConfig = new \Wirecard\PaymentSdk\Config\PaymentMethodConfig(
            'unionpayinternational',
            'merchant_account_id',
            'secret'
        );
        $config->add($expectedPaymentConfig);

        $transaction = $payment->createTransaction($this->payment, $cart, $paymentType, $orderId);

        $actual = $payment->processCreditCard($config, $transaction, $orderId, $paymentType);
        $expected = [
            'orderId' => '55555',
            'requestData' => [
                'transaction_type' => 'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_PAYMENT_ACTION',
                'merchant_account_id' => 'merchant_account_id',
                'requested_amount' => 200,
                'requested_amount_currency' => 'EUR',
                'locale' => 'en',
                'payment_method' => 'creditcard',
                'attempt_three_d' => false,
                'street1' => null,
                'city' => null,
                'country' => null,
                'shipping_street1' => null,
                'shipping_city' => null,
                'shipping_country' => null,
                'orderItems1.name' => 'Test1',
                'orderItems1.quantity' => 20,
                'orderItems1.amount.value' => 10,
                'orderItems1.amount.currency' => 'EUR',
                'orderItems1.articleNumber' => '003',
                'orderItems1.taxRate' => '0.00',
                'field_name_1' => 'paysdk_orderId',
                'field_value_1' => '55555',
                'notification_transaction_url' => 'http://test.com',
                'notifications_format' => 'application/xml',
                'descriptor' => 'PSSHOPNAM 55555',
                'order_number' => '55555',
                'ip_address' => '127.0.0.1'
            ],
            'paymentPageLoader' =>
                'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_BASE_URL/engine/hpp/paymentPageLoader.js',
            'actionUrl' => 'http://test.com'
        ];

        $actual['requestData'] = json_decode($actual['requestData'], true);
        //unset the generated request id,timestamp and signature as it is different every time
        unset(
            $actual['requestData']['request_id'],
            $actual['requestData']['request_signature'],
            $actual['requestData']['request_time_stamp']
        );

        $this->assertEquals($expected, $actual);
    }
}
