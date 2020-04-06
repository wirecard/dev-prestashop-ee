<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Helper\TransactionBuilder;

class TransactionBuilderTest extends PHPUnit_Framework_TestCase
{
    private $transaction;

    private $context;

    public function setUp()
    {
        $this->context = new Context();
        $this->context->cart->setId(123);
    }

    public function testCreditCardOrderNumber()
    {
        $transactionBuilder = new TransactionBuilder('creditcard');
        $transactionBuilder->setContext($this->context);
        $transactionBuilder->setOrderId('321');
        $this->transaction = $transactionBuilder->buildTransaction();
        $this->assertEquals($this->transaction->getOrderNumber(), '321');
    }
}
