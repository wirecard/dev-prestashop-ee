<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Service;

use Wirecard\ExtensionOrderStateModule\Application\Mapper\GenericOrderStateMapper;
use Wirecard\ExtensionOrderStateModule\Application\Service\OrderState;
use WirecardEE\Prestashop\Classes\Config\OrderStateMappingDefinition;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\OrderStateTransferObject;

/**
 * Class OrderStateManagerService
 * @package WirecardEE\Prestashop\Classes\Service
 * @since 2.10.0
 */
class OrderStateManagerService implements ServiceInterface
{
    /**
     * @var OrderState
     */
    protected $service;

    /**
     * OrderStateManagerService constructor.
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    public function __construct()
    {
        $orderStateMapper = new GenericOrderStateMapper(new OrderStateMappingDefinition());
        $this->service = new OrderState($orderStateMapper);
    }

    /**
     * @param int $currentOrderState
     * @param string $processType
     * @param array $transactionResponse
     * @param OrderStateNumericalValues $numericalValues
     * @return int|mixed|string
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException
     */
    public function calculateNextOrderState(
        $currentOrderState,
        $processType,
        array $transactionResponse,
        OrderStateNumericalValues $numericalValues
    ) {
        $input = new OrderStateTransferObject($currentOrderState, $processType, $transactionResponse, $numericalValues);
        // #TEST_STATE_LIBRARY
        (new Logger())->debug(print_r($input, true), ['method' => __METHOD__, 'line' => __LINE__]);
        return $this->service->process($input);
    }
}
