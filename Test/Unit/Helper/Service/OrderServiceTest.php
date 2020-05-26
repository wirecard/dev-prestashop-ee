<?php

use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class OrderServiceTest extends \PHPUnit_Framework_TestCase
{
    const TRANSACTION_ID = '0696a9fb-8c2a-4466-9436-6955bbf569d2';
    const AMOUNT = 12.34;
    /**
     * @var Order
     */
    private $order;

    protected function setUp()
    {
        $this->order = new \Order();
    }

    /**
     * @dataProvider getOrderStatesAndExpectedValues
     * @param string $orderState
     * @param bool $expectedValue
     * @throws ReflectionException
     */
    public function testCreateOrderPayment($orderState, $expectedValue)
    {
        //@todo do refactoring
        $this->order->setCurrentState($orderState);

        /** @var OrderAmountCalculatorService $orderAmountCalculatorService */
        $orderAmountCalculatorService = $this->getMockBuilder(OrderAmountCalculatorService::class)
            ->disableOriginalConstructor()
            ->setMethods(["getOrderRefundedAmount"])
            ->getMock();
        $orderAmountCalculatorService->method("getOrderRefundedAmount")
            ->willReturn(self::AMOUNT);

        $orderService = new OrderService($this->order);
        $orderService->setOrderAmountCalculatorService($orderAmountCalculatorService);
        $result = $orderService->createOrderPayment($this->getResponse(), self::AMOUNT);

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * @return SuccessResponse
     * @throws ReflectionException
     */
    private function getResponse()
    {
        /** @var SuccessResponse $response */
        $response = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();
        $this->setProperties(
            SuccessResponse::class,
            $response,
            [
                'transactionId' => self::TRANSACTION_ID
            ]
        );
        return $response;
    }

    /**
     * @return array
     */
    public function getOrderStatesAndExpectedValues()
    {
        return [
            [_PS_OS_REFUND_, true],
            [OrderManager::WIRECARD_OS_PARTIALLY_REFUNDED, true],
            [OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED, true],
            [OrderManager::WIRECARD_OS_AUTHORIZATION, false],
        ];
    }

    /**
     * @param mixed $class
     * @param $object
     * @param $properties
     * @throws ReflectionException
     */
    private function setProperties($class, $object, $properties)
    {
        $reflection = new ReflectionClass($class);
        foreach ($properties as $name => $value) {
            $reflection_property = $reflection->getProperty($name);
            $reflection_property->setAccessible(true);
            $reflection_property->setValue($object, $value);
        }
    }
}
