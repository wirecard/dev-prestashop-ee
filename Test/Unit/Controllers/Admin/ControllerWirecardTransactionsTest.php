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

require_once __DIR__ . '/../../../../wirecardpaymentgateway/controllers/admin/WirecardTransactions.php';

class ControllerWirecardTransactionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WirecardTransactionsController
     */
    public $wirecardTransactionsController;

    public function setUp()
    {
        $beckendService = \Mockery::mock('overload:'. BackendService::class);
        $beckendService->shouldReceive('retrieveBackendOperations')
            ->andReturn(
                [
                    Operation::REFUND,
                    Operation::CANCEL,
                    Operation::PAY
                ]
            );

        $this->wirecardTransactionsController = new WirecardTransactionsController();
    }

    public function testConstructor()
    {
        $this->assertNotNull($this->wirecardTransactionsController);
    }

    public function testRenderView()
    {
        $this->wirecardTransactionsController->renderView();

        $expected = [
            'current_index' => '1',
            'payment_method' => 'Wirecard Credit Card',
            'possible_operations' => [
                [
                    'action' => 0,
                    'name' => 'text_refund_transaction'
                ], [
                    'action' => 1,
                    'name' => 'text_cancel_transaction'
                ], [
                    'action' => 2,
                    'name' => 'text_capture_transaction'
                ],
            ],
            'back_link' => 'WirecardTransactions',
            'transaction' => [
                'tx' => '11',
                'id' => '12l3j123kjg12kj3g123',
                'type' => 'authorization',
                'status' => 'success',
                'amount' => '20',
                'currency' => 'EUR',
                'response' => null,
                'payment_method' => 'creditcard',
                'order' => '12',
                'badge' => 'red'
            ]
        ];

        $this->assertEquals($expected, $this->wirecardTransactionsController->tpl_view_vars);
    }
}
