<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config;

use Wirecard\ExtensionOrderStateModule\Domain\Contract\MappingDefinition;
use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * Class OrderStateMappingDefinition
 * @package WirecardEE\Prestashop\Classes\Config
 * @since 2.10.0
 */
class OrderStateMappingDefinition implements MappingDefinition
{
    /**
     * @inheritDoc
     */
    public function definitions()
    {
        return [
            _PS_OS_PAYMENT_ => Constant::ORDER_STATE_PROCESSING,
            _PS_OS_PENDING_ => Constant::ORDER_STATE_PENDING,
            _PS_OS_ERROR_ => Constant::ORDER_STATE_FAILED,
            OrderManager::WIRECARD_OS_STARTING => Constant::ORDER_STATE_STARTED,
            OrderManager::WIRECARD_OS_AUTHORIZATION => Constant::ORDER_STATE_AUTHORIZED,
        ];
    }
}
