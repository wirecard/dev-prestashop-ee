<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

require_once __DIR__ . '/../../../../../wirecardpaymentgateway/wirecardpaymentgateway.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction as SdkTransaction;
use WirecardEE\Prestashop\Classes\Transaction\Builder\PostProcessingTransactionBuilder;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Models\PaymentCreditCard;
use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Models\PaymentSofort;
use WirecardEE\Prestashop\Models\Transaction;

class PostProcessingTransactionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Transaction  */
    private $transaction;

    public function setUp()
    {
        $this->transaction = Mockery::mock(Transaction::class);
        $this->transaction->shouldReceive(
            [
                'getOrderNumber' => '12',
                'getAmount' => 20,
                'getCurrency' => 'EUR',
                'getCartId' => 11,
                'getTransactionId' => 'parent_transaction_id',
                'getResponse' => file_get_contents("Test/Stubs/ratepay_get_response_data_stub.json")
            ]
        )->once();
    }

    /**
     * @dataProvider builderProvider
     *
     * @param Payment $payment
     * @param string $operation
     * @param SdkTransaction $expectedTransaction
     * @throws Exception
     */
    public function testBuild($payment, $operation, $expectedTransaction)
    {
        $postProcessingTransactionBuilder = new PostProcessingTransactionBuilder($payment, $this->transaction);
        $postProcessingTransactionBuilder->setOperation($operation);
        $actualTransaction = $postProcessingTransactionBuilder->build();
        $this->assertEquals($expectedTransaction, $actualTransaction);
        $this->assertInstanceOf(get_class($expectedTransaction), $actualTransaction);
    }

    public function builderProvider()
    {
        return [
            /*[ payment | operation | expectedTransaction ]*/
            $this->prepareDataForCreditCardRefundTransaction(),
            $this->prepareDataForSofortRefundTransaction(),
            $this->prepareDataForGuaranteedInvoiceRatepay()
        ];
    }

    private function prepareDataForCreditCardRefundTransaction()
    {
        $payment = new PaymentCreditCard();
        $expectedTransaction = new CreditCardTransaction();
        $expectedTransaction->setParentTransactionId('parent_transaction_id');
        $expectedTransaction->setAmount(new Amount(20, 'EUR'));

        return [
            $payment,
            Operation::REFUND,
            $expectedTransaction
        ];
    }

    private function prepareDataForSofortRefundTransaction()
    {
        $payment = new PaymentSofort();
        //For refund Sofort. payment is a SEPA credit Transaction and operation credit
        $expectedTransaction = new SepaCreditTransferTransaction();
        $expectedTransaction->setParentTransactionId('parent_transaction_id');
        $expectedTransaction->setAmount(new Amount(20, 'EUR'));

        return [
            $payment,
            Operation::CREDIT,
            $expectedTransaction
        ];
    }

    private function prepareDataForGuaranteedInvoiceRatepay()
    {
        $payment = new PaymentGuaranteedInvoiceRatepay();
        $expectedTransaction = new RatepayInvoiceTransaction();
        $expectedTransaction->setParentTransactionId('parent_transaction_id');
        $expectedTransaction->setAmount(new Amount(20, 'EUR'));

        $item = new Item(
            'test item 1',
            new Amount(20, 'EUR'),
            1
        );
        $item->setTaxRate(20);
        $item->setDescription('lalalalala');
        $item->setArticleNumber('1234');

        $basket = new Basket();
        $basket->add($item);
        $basket->setVersion($expectedTransaction);
        $expectedTransaction->setBasket($basket);

        return [
            $payment,
            Operation::REFUND,
            $expectedTransaction
        ];
    }
}
