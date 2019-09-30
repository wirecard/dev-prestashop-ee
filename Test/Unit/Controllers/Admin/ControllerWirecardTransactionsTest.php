<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

require_once __DIR__ . '/../../../../wirecardpaymentgateway/controllers/admin/WirecardTransactions.php';

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
        $this->transactions->renderView();
        $expected = array(
            'current_index' => '1',
            'transaction_id' => '12l3j123kjg12kj3g123',
            'payment_method' => 'Wirecard Credit Card',
            'transaction_type' => 'authorization',
            'status' => 'success',
            'amount' => '20',
            'currency' => 'EUR',
            'response_data' => null,
            'canCancel' => true,
            'canCapture' => true,
            'canRefund' => false,
            'cancelLink' => 'WirecardTransactions',
            'captureLink' => 'WirecardTransactions',
            'refundLink' => 'WirecardTransactions',
            'backButton' => 'WirecardTransactions'

        );

        $this->assertEquals($expected, $this->transactions->tpl_view_vars);
    }
}
