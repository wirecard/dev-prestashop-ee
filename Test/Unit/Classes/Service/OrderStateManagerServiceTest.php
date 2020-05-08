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
use WirecardEE\Prestashop\Classes\Service\OrderStateManagerService;
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;
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
    public function oneStepScenarioDataProvider()
    {
        $numericalValues = new OrderStateNumericalValues(42);
        yield [
            \Configuration::get(OrderManager::WIRECARD_OS_STARTING),
            Constant::PROCESS_TYPE_INITIAL_RETURN,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_DEBIT,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            $numericalValues,
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING)
        ];

        yield [
            \Configuration::get(OrderManager::WIRECARD_OS_STARTING),
            Constant::PROCESS_TYPE_INITIAL_RETURN,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_DEBIT,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_FAILED,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            $numericalValues,
            _PS_OS_ERROR_
        ];

        yield [
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING),
            Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_DEBIT,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            $numericalValues,
            _PS_OS_PAYMENT_
        ];

        yield [
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING),
            Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_AUTHORIZE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_SUCCESS,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            $numericalValues,
            \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION)
        ];

        yield [
            _PS_OS_ERROR_,
            Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
            [
                OrderStateTransferObject::FIELD_TRANSACTION_TYPE => Constant::TRANSACTION_TYPE_AUTHORIZE,
                OrderStateTransferObject::FIELD_TRANSACTION_STATE => Constant::TRANSACTION_STATE_FAILED,
                OrderStateTransferObject::FIELD_REQUESTED_AMOUNT => 42,
            ],
            $numericalValues,
            _PS_OS_ERROR_
        ];
    }

    /**
     * @group unit
     * @small
     * @dataProvider oneStepScenarioDataProvider
     * @param int $currentState
     * @param string $processType
     * @param array $transactionResponse
     * @param OrderStateNumericalValues $numericalValues
     * @param int $expectedNextState
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException
     */
    public function testCalculateNextOrderState(
        $currentState,
        $processType,
        $transactionResponse,
        OrderStateNumericalValues $numericalValues,
        $expectedNextState
    ) {
        $nextState = $this->object->calculateNextOrderState(
            $currentState,
            $processType,
            $transactionResponse,
            $numericalValues
        );
        $this->assertEquals($expectedNextState, $nextState);
    }
}
