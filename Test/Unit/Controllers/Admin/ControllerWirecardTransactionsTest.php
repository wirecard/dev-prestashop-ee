<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\Operation;
use WirecardEE\Prestashop\Models\Transaction;

require_once __DIR__ . '/../../../../wirecardpaymentgateway/controllers/admin/WirecardTransactions.php';

class ControllerWirecardTransactionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WirecardTransactionsController
     */
    public $wirecardTransactionsController;

    public function setUp()
    {
        $beckendService = \Mockery::mock('overload:' . BackendService::class);
        $beckendService->shouldReceive('retrieveBackendOperations')
            ->andReturn(
                [
                    Operation::REFUND => 'Refund',
                    Operation::CANCEL => 'Cancel',
                    Operation::PAY => 'Capture'
                ]
            );

        $this->wirecardTransactionsController = new WirecardTransactionsController();

        $this->wirecardTransactionsController->object = $this->getTestTransaction();
    }

    /**
     * @return Transaction
     */
    protected function getTestTransaction()
    {
        $transaction = new Transaction();
        $transaction->setPaymentMethod('creditcard');
        $transaction->setTxId(11);
        $transaction->setAmount(20);
        $transaction->setCurrency('EUR');
        $transaction->setOrderNumber(12);
        $transaction->setTransactionType('authorization');
        $transaction->setTransactionState('success');
        $transaction->setTransactionId('QWERTY123XYZAABB1122');
        return $transaction;
    }

    public function testConstructor()
    {
        $this->assertNotNull($this->wirecardTransactionsController);
    }

    public function testRenderView()
    {
        $this->wirecardTransactionsController->renderView();
        $transaction = $this->getTestTransaction();

        $expected = [
            'current_index' => '1',
            'payment_method' => 'Carte',
            'possible_operations' => [
                [
                    'action' => 'refund',
                    'name' => 'Refund transaction'
                ], [
                    'action' => 'cancel',
                    'name' => 'Cancel transaction'
                ], [
                    'action' => 'pay',
                    'name' => 'Capture transaction'
                ],
            ],
            'back_link' => 'WirecardTransactions',
            'transaction' => [
                'tx' => $transaction->getTxId(),
                'id' => $transaction->getTransactionId(),
                'type' => $transaction->getTransactionType(),
                'status' => $transaction->getTransactionState(),
                'amount' => $transaction->getAmount(),
                'currency' => $transaction->getCurrency(),
                'response' => null,
                'payment_method' => $transaction->getPaymentMethod(),
                'order' => $transaction->getOrderNumber(),
                'badge' => 'red'
            ],
            'remaining_delta_amount' => 0.0,
            'precision' => 2,
            'step' => '0.01',
            'regex' => '/^[+]?(?=.?\d)\d*(\.\d{0,2})?$/',
        ];

        $this->assertEquals($expected, $this->wirecardTransactionsController->tpl_view_vars);
    }
}
