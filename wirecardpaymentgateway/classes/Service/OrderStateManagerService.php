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
    private $service;

    /**
     * OrderStateManagerService constructor.
     * @param OrderStateMappingDefinition $mappingDefinition
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * @param array $data
     * @return int|mixed|string
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException
     */
    public function calculateNextOrderState(array $data)
    {
        $input = (new OrderStateTransferObject())->initFromData($data);
        return $this->service->process($input);
    }

    /**
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    private function initialize()
    {
        $mappingDefinition = new OrderStateMappingDefinition();
        $orderStateMapper = new GenericOrderStateMapper($mappingDefinition);
        $this->service = new OrderState($orderStateMapper);
    }
}
