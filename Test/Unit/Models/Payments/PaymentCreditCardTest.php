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

        $expected = 'Wirecard Credit Card';

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

    public function testGetRequestData()
    {
        $context = new Context();

        $expected = array(
            'transaction_type' => 'authorization',
            'merchant_account_id' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
            'requested_amount' => 20,
            'requested_amount_currency' => 'EUR',
            'locale' => 'en',
            'payment_method' => 'creditcard',
            'attempt_three_d' => false,
            'ip_address' => '127.0.0.1',
            'descriptor' => 'PSSHOPNAM123',
            'field_name_1' => 'paysdk_cartId',
            'field_value_1' => 123,
            'shop_system_name' => EXPECTED_SHOP_NAME,
            'shop_system_version' => _PS_VERSION_,
            'plugin_name' => EXPECTED_PLUGIN_NAME,
            'date_of_birth' => '01-01-1980',
            'city' => null,
            'country' => null,
            'shipping_city' => null,
            'shipping_country' => null,
            'order_number' => 123,
            'consumer_id' => 1,
            'email'=>'max.mustermann@email.com',
            'authentication_method' => '02',
            'authentication_timestamp' => '2019-08-04T02:37:40Z',
            'challenge_indicator' => '02',
            'account_creation_date' => '2019-06-03',
            'account_update_date' => '2019-06-09',
            'account_password_change_date' => '2019-08-09',
            'shipping_address_first_use' => '2019-08-09',
            'purchases_last_six_months' => 3,
            'merchant_crm_id' => '',
            'card_creation_date' => date('Y-m-d'),
            'orderItems1.name'            => 'Product 1',
            'orderItems1.quantity'        => 1,
            'orderItems1.amount.value'    => 2,
            'orderItems1.amount.currency' => 'EUR',
            'orderItems1.articleNumber'   => 'reference',
            'orderItems1.taxRate'         => -4900,
            'risk_info_delivery_mail'     => 'max.mustermann@email.com',
            'risk_info_reorder_items'     => '02',
            'risk_info_availability'     => '01'
            'plugin_version' => \WirecardPaymentGateway::VERSION,
        );

        $actual = (array) json_decode($this->payment->getRequestData($this->paymentModule, $context, 123));
        //unset the generated request id as it is different every time
        unset($actual['request_id'], $actual['request_signature'], $actual['request_time_stamp']);

        $this->assertEquals($expected, $actual);
    }

    public function testCreateCancelTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\CreditCardTransaction();
        $actual->setParentTransactionId('my_secret_id');

        $this->assertEquals($actual, $this->payment->createCancelTransaction($this->transactionData));
    }

    public function testCreatePayTransaction()
    {
        $actual = new \Wirecard\PaymentSdk\Transaction\CreditCardTransaction();
        $actual->setParentTransactionId('my_secret_id');

        $this->assertEquals($actual, $this->payment->createPayTransaction($this->transactionData));
    }
}
