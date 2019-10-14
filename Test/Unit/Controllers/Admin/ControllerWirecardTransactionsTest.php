<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

require_once __DIR__ . '/../../../../wirecardpaymentgateway/controllers/admin/WirecardTransactions.php';

use Mockery as m;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\Operation;

class ControllerWirecardTransactionsTest extends \PHPUnit_Framework_TestCase
{
    public $transactions;

    public function setUp()
    {
        $this->transactions = new WirecardTransactionsController();
    }

    public function testConstructor()
    {
        $this->assertNotNull($this->transactions);
    }

    public function testRenderView()
    {
        $backendServiceMock = m::mock('overload:' . BackendService::class);
        $backendServiceMock->shouldReceive('retrieveBackendOperations')
            ->andReturn([
                Operation::PAY => "Pay"
            ]);

        $this->transactions->renderView();
        $expected = array(
            'current_index' => '1',
            'payment_method' => 'Wirecard Credit Card',
            'possible_operations' => array(
                array(
                    'action' => 'pay',
                    'name' => 'Capture transaction'
                )
            ),
            'transaction' => array(
                'tx' => 11,
                'id' => '12l3j123kjg12kj3g123',
                'type' => 'authorization',
                'status' => 'open',
                'amount' => '20',
                'currency' => 'EUR',
                'response' => array('key' => 'value'),
                'payment_method' => 'creditcard',
                'order' => 'ABCDEFG',
                'badge' => 'green'
            ),
            'back_link' => 'WirecardTransactions'
        );

        $this->assertEquals($expected, $this->transactions->tpl_view_vars);
    }
}
