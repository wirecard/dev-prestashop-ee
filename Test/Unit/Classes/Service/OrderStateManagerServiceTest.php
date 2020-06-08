<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Test\Classes\Service;

use Wirecard\ExtensionOrderStateModule\Application\Mapper\GenericOrderStateMapper;
use Wirecard\ExtensionOrderStateModule\Application\Service\OrderState;
use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use WirecardEE\Prestashop\Classes\Config\OrderStateMappingDefinition;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Classes\Service\OrderStateManagerService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\OrderStateTransferObject;

/**
 * Class OrderStateManagerServiceTest
 */
class OrderStateManagerServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | OrderStateManagerService
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | OrderAmountCalculatorService
     */
    protected $orderAmountCalculator;

    /**
     * @throws \ReflectionException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    protected function setUp()
    {
        $this->object = $this->getMockBuilder(OrderStateManagerService::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['calculateNextOrderState'])->getMock();
        $reflection = new \ReflectionClass($this->object);
        $reflection_property = $reflection->getProperty("service");
        $reflection_property->setAccessible(true);
        $orderStateMapper = new GenericOrderStateMapper(new OrderStateMappingDefinition());
        $reflection_property->setValue($this->object, new OrderState($orderStateMapper));
    }


    /**
     * @return \Generator
     */
    public function oneStepInitialPaymentScenarioDataProvider()
    {
        yield "Initial payment. Order awaiting for notification." => [
            \Configuration::get(OrderManager::WIRECARD_OS_STARTING),
            Constant::PROCESS_TYPE_INITIAL_RETURN,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_DEBIT,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            [100, 0, 0],
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING)
        ];

        yield "Order state started. Failed due to transaction was unsuccessful" => [
            \Configuration::get(OrderManager::WIRECARD_OS_STARTING),
            Constant::PROCESS_TYPE_INITIAL_RETURN,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_DEBIT,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_FAILED,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            [100, 0, 0],
            _PS_OS_ERROR_
        ];

        yield "Order state in pending. Debit transaction is successful. Next state: processing" => [
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING),
            Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_DEBIT,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            [100, 0, 0],
            _PS_OS_PAYMENT_
        ];

        yield "Order state in pending. Authorization transaction is successful. Next state: authorization" => [
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING),
            Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_AUTHORIZATION,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            [100, 0, 0],
            \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION)
        ];

        yield "Order payment due to technical error failed. Order state is still failed." => [
            _PS_OS_ERROR_,
            Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_AUTHORIZATION,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_FAILED,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            [100, 0, 0],
            _PS_OS_ERROR_
        ];
    }


    /**
     * @return \Generator
     */
    public function oneStepPostProcessingScenarioDataProvider()
    {

        yield "Post processing partially capture operation. Next state: partially captured" => [
            \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_CAPTURE_AUTHORIZATION,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 50,
            ],
            [100, 0, 0],
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED),
        ];
        yield "Post processing partially capture operation reaches full amount. Next state: processing" => [
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_CAPTURE_AUTHORIZATION,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 50,
            ],
            [100, 50, 0],
            _PS_OS_PAYMENT_,
        ];
        yield "Post processing partially refund operation. Next state: partially refunded" => [
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_REFUND_CAPTURE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 10,
            ],
            [100, 50, 40],
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_REFUNDED),
        ];
        yield "Post processing partially refund operation after full capture. Next state: partially refunded" => [
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_REFUND_CAPTURE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 30,
            ],
            [100, 100, 10],
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_REFUNDED),
        ];
        yield "Post processing partially refund less as capture amount. Next state: partially capture" => [
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_REFUNDED),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_REFUND_CAPTURE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 30,
            ],
            [100, 50, 10],
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED),
        ];
        yield "Post processing partially refund operation reaches full refund amount. Next state: refunded" => [
            \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_REFUND_CAPTURE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 60,
            ],
            [100, 100, 40],
            _PS_OS_REFUND_
        ];
        yield "Post processing cancel authorization. Next state: canceled" => [
            \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_VOID_AUTHORIZATION,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 100,
            ],
            [100, 0, 0],
            _PS_OS_CANCELED_
        ];
        yield "Post processing full refund authorization. Next state: refunded" => [
            \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION),
            Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_REFUND_CAPTURE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 100,
            ],
            [100, 0, 0],
            _PS_OS_REFUND_
        ];
    }

    /**
     * @group unit
     * @small
     * @dataProvider oneStepInitialPaymentScenarioDataProvider
     * @dataProvider oneStepPostProcessingScenarioDataProvider
     * @param int $currentState
     * @param string $processType
     * @param array $transactionResponse
     * @param array $orderAmountValues
     * @param int $expectedNextState
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException
     */
    public function testCalculateNextOrderState(
        $currentState,
        $processType,
        $transactionResponse,
        $orderAmountValues,
        $expectedNextState
    ) {
        list($orderTotalAmount, $orderCapturedAmount, $orderRefundedAmount) = $orderAmountValues;
        $this->orderAmountCalculator = $this->getMockBuilder(OrderAmountCalculatorService::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderAmountCalculator->method('getOrderTotalAmount')->willReturn($orderTotalAmount);
        $this->orderAmountCalculator->method('getOrderCapturedAmount')->willReturn($orderCapturedAmount);
        $this->orderAmountCalculator->method('getOrderRefundedAmount')->willReturn($orderRefundedAmount);

        $nextState = $this->object->calculateNextOrderState(
            $currentState,
            $processType,
            $transactionResponse,
            $this->orderAmountCalculator
        );
        $this->assertEquals($expectedNextState, $nextState);
    }
}
