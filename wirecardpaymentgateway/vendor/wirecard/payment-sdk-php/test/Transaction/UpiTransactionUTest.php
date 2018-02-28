<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace WirecardTest\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\UpiTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Transaction;

class UpiTransactionUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpiTransaction
     */
    private $tx;

    /**
     * @var UpiConfig
     */
    private $config;

    public function setUp()
    {
        $this->config = new PaymentMethodConfig(UpiTransaction::NAME, 'maid', 'secret');
        $this->tx = new UpiTransaction();
        $this->tx->setConfig($this->config);
    }

    public function testSetTermUrl()
    {
        $this->tx->setTermUrl('test');
        $this->assertAttributeEquals('test', 'termUrl', $this->tx);
    }

    public function testGetTermUrl()
    {
        $this->tx->setTermUrl('test');
        $this->assertEquals('test', $this->tx->getTermUrl());
    }

    public function testGetEndpoint()
    {
        $expected = Transaction::ENDPOINT_PAYMENTS;
        $result = $this->tx->getEndpoint();
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testMapPropertiesUnsupportedOperation()
    {
        $this->tx->setTokenId('anything');

        $this->tx->setOperation('non-existing');
        $this->tx->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testMapPropertiesNoTokenIdNoParentTransactionId()
    {
        $this->tx->setOperation(Operation::RESERVE);
        $this->tx->mappedProperties();
    }

    public function testSslUpiTransactionWithTokenId()
    {
        $_SERVER['REMOTE_ADDR'] = 'test IP';

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'transaction-type' => 'authorization',
            'card-token' => [
                'token-id' => '21'
            ],
            'ip-address' => 'test IP',
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];

        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setTokenId('21');
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setOperation(Operation::RESERVE);

        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testSslUpiTransactionWithParentTransactionId()
    {

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'requested-amount' => ['currency' => 'EUR', 'value' => 24],
            'parent-transaction-id' => 'parent5',
            'transaction-type' => 'referenced-authorization',
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];

        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setAmount(new Amount(24, 'EUR'));
        $transaction->setParentTransactionId('parent5');
        $transaction->setParentTransactionType(Transaction::TYPE_AUTHORIZATION);
        $transaction->setOperation(Operation::RESERVE);
        $result = $transaction->mappedProperties();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function testCancelProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                Transaction::TYPE_REFERENCED_AUTHORIZATION,
                Transaction::TYPE_VOID_AUTHORIZATION
            ],
            [
                'refund-capture',
                'void-refund-capture'
            ],
            [
                'refund-purchase',
                'void-refund-purchase'
            ],
            [
                UpiTransaction::TYPE_PURCHASE,
                'void-purchase'
            ],
            [
                UpiTransaction::TYPE_REFERENCED_PURCHASE,
                'void-purchase'
            ],
            [
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
                'void-capture'
            ]
        ];
    }

    /**
     * @dataProvider testCancelProvider
     * @param $transactionType
     * @param $cancelType
     */
    public function testCancel($transactionType, $cancelType)
    {
        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::CANCEL);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $cancelType,
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testCancelNoParentId()
    {
        $transaction = new UpiTransaction();
        $transaction->setOperation(Operation::CANCEL);
        $transaction->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testCancelInvalidParentTransaction()
    {
        $transaction = new UpiTransaction();
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType('test');
        $transaction->setOperation(Operation::CANCEL);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $transaction->mappedProperties();
    }

    public function testPayProvider()
    {
        return [
            [
                Transaction::TYPE_AUTHORIZATION,
                Transaction::TYPE_CAPTURE_AUTHORIZATION
            ],
            [
                UpiTransaction::TYPE_PURCHASE,
                UpiTransaction::TYPE_REFERENCED_PURCHASE
            ],
            [
                null,
                UpiTransaction::TYPE_PURCHASE
            ]
        ];
    }

    /**
     * @dataProvider testPayProvider
     * @param $transactionType
     * @param $payType
     */
    public function testPay($transactionType, $payType)
    {
        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::PAY);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $payType,
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function testRefundProvider()
    {
        return [
            [
                UpiTransaction::TYPE_PURCHASE,
                'refund-purchase'
            ],
            [
                UpiTransaction::TYPE_REFERENCED_PURCHASE,
                'refund-purchase'
            ],
            [
                Transaction::TYPE_CAPTURE_AUTHORIZATION,
                'refund-capture'
            ]
        ];
    }

    /**
     * @dataProvider testRefundProvider
     * @param $transactionType
     * @param $refundType
     */
    public function testRefund($transactionType, $refundType)
    {
        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType($transactionType);
        $transaction->setOperation(Operation::REFUND);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $result = $transaction->mappedProperties();

        $expectedResult = [
            'payment-methods' => ['payment-method' => [['name' => 'creditcard']]],
            'parent-transaction-id' => '642',
            'ip-address' => 'test',
            'transaction-type' => $refundType,
            'entry-mode' => 'ecommerce',
            'locale' => 'de',
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    public function testRefundNoParentId()
    {
        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setOperation(Operation::REFUND);
        $transaction->mappedProperties();
    }

    /**
     * @expectedException \Wirecard\PaymentSdk\Exception\UnsupportedOperationException
     */
    public function testRefundInvalidParentTransaction()
    {
        $transaction = new UpiTransaction();
        $transaction->setConfig($this->config);
        $transaction->setParentTransactionId('642');
        $transaction->setParentTransactionType('test');
        $transaction->setOperation(Operation::REFUND);
        $_SERVER['REMOTE_ADDR'] = 'test';

        $transaction->mappedProperties();
    }

    public function testRetrieveOperationTypeAuthorization()
    {
        $tx = new UpiTransaction();
        $tx->setConfig($this->config);
        $tx->setOperation(Operation::RESERVE);

        $this->assertEquals(Transaction::TYPE_AUTHORIZATION, $tx->retrieveOperationType());
    }

    public function testRetrieveOperationTypePurchase()
    {
        $tx = new UpiTransaction();
        $tx->setConfig($this->config);
        $tx->setOperation(Operation::PAY);

        $this->assertEquals(UpiTransaction::TYPE_PURCHASE, $tx->retrieveOperationType());
    }
}
