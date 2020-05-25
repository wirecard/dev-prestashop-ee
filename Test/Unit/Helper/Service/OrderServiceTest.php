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
    const TRANSACTION_ID = '12345678asdfgh';
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
     * @param $orderState
     * @param $expectedValue
     */
    public function testCreateOrderPayment($orderState, $expectedValue)
    {
        $this->order->setCurrentState($orderState);
        $successResponse = $this->getTransaction();

        $orderAmountCalculatorService = $this->getMockBuilder(OrderAmountCalculatorService::class)
            ->disableOriginalConstructor()
            ->setMethods(["getOrderRefundedAmount"])
            ->getMock();
        $orderAmountCalculatorService->method("getOrderRefundedAmount")
            ->willReturn((float)5);

        $orderService = new OrderService($this->order);
        $result = $orderService->createOrderPayment($successResponse, self::AMOUNT, $orderAmountCalculatorService);

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * @return SuccessResponse
     */
    private function getTransaction()
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

    public function getOrderStatesAndExpectedValues()
    {
        return [
            [_PS_OS_REFUND_, true],
            [OrderManager::WIRECARD_OS_PARTIALLY_REFUNDED, true],
            [OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED, true],
            [OrderManager::WIRECARD_OS_AUTHORIZATION, false],
        ];
    }

    public function setProperties($class, $object, $properties)
    {
        $reflection = new ReflectionClass($class);
        foreach ($properties as $name => $value) {
            $reflection_property = $reflection->getProperty($name);
            $reflection_property->setAccessible(true);
            $reflection_property->setValue($object, $value);
        }
    }
}
