<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Helper\TransactionBuilder;

/**
 * Class TransactionBuilderTest
 * @package WirecardEE\Test\Prestashop\Helper
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\TransactionBuilder
 */
class TransactionBuilderTest extends PHPUnit_Framework_TestCase
{
    const PAYMENT_METHOD_CREDIT_CARD = "creditcard";
    const DEMO_ORDER_NUMBER = "321";
    /**
     * @var TransactionBuilder
     */
    private $transaction;

    /**
     * @var Context
     */
    private $context;

    public function setUp()
    {
        $this->context = new Context();
        $this->context->cart->setId(123);
    }

    /**
     * @group unit
     * @small
     * @covers ::creditCardOrderNumber
     */
    public function testCreditCardOrderNumber()
    {
        $transactionBuilder = new TransactionBuilder(self::PAYMENT_METHOD_CREDIT_CARD);
        $transactionBuilder->setContext($this->context);
        $transactionBuilder->setOrderId(self::DEMO_ORDER_NUMBER);
        $this->transaction = $transactionBuilder->buildTransaction();
        $this->assertEquals($this->transaction->getOrderNumber(), self::DEMO_ORDER_NUMBER);
    }
}
