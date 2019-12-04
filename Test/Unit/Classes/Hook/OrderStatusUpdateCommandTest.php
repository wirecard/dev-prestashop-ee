<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Test\Classes\Hook\OrderStatusPostUpdateCommand;

use WirecardEE\Prestashop\Classes\Hook\OrderStatusUpdateCommand;

/**
 * Class OrderStatusUpdateCommandTest
 * @package WirecardEE\Prestashop\Classes\Hook
 * @coversDefaultClass  \WirecardEE\Prestashop\Classes\Hook\OrderStatusUpdateCommand
 */
class OrderStatusUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     * @small
     * @covers ::getOrderId
     * @covers ::getOrderState
     * @throws \Exception
     */
    public function testConstructor()
    {

        $orderState = new \OrderState();
        $orderState->name = 'shipped';
        $command = new OrderStatusUpdateCommand($orderState, 123);
        $this->assertNotEmpty($command->getOrderState());
        $this->assertInstanceOf(\OrderState::class, $command->getOrderState());
        $this->assertNotEmpty($command->getOrderId());
        $this->assertInternalType('int', $command->getOrderId());
        $this->assertEquals(123, $command->getOrderId());
    }


    /**
     * @group unit
     * @small
     * @covers ::getOrderState
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testThrowExceptionNotInstanceOfOrderState()
    {
        new OrderStatusUpdateCommand(null, 123);
    }

    /**
     * @group unit
     * @small
     * @covers ::getOrderId
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testThrowExceptionNotNumericOrderID()
    {
        $orderState = new \OrderState();
        $orderState->name = 'shipped';
        new OrderStatusUpdateCommand($orderState, "XYZ123");
    }
}
