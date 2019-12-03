<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Test\Prestashop\Classes\Hook\OrderStatusPostUpdateCommand;

use WirecardEE\Prestashop\Classes\Hook\OrderStatusPostUpdateCommand;

/**
 * Class OrderStatusPostUpdateCommandTest
 * @package WirecardEE\Prestashop\Classes\Hook
 * @coversDefaultClass  \WirecardEE\Prestashop\Classes\Hook\OrderStatusPostUpdateCommand
 */
class OrderStatusPostUpdateCommandTest extends \PHPUnit_Framework_TestCase
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
        $command = new OrderStatusPostUpdateCommand($orderState, 123);
        $this->assertNotEmpty($command->getOrderState());
        $this->assertInstanceOf(\OrderState::class, $command->getOrderState());
        $this->assertNotEmpty($command->getOrderId());
        $this->assertTrue(is_numeric($command->getOrderId()));
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
        $command = new OrderStatusPostUpdateCommand(null, 123);
    }

    /**
     * @group unit
     * @small
     * @covers ::getOrderId
     * @expectedException \Exception
     * @throws \Exception
     */
    public function testthrowExceptionNotNumericOrderID()
    {
        $orderState = new \OrderState();
        $orderState->name = 'shipped';
        $command = new OrderStatusPostUpdateCommand($orderState, "XYZ123");
    }
}
