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
            _PS_OS_ERROR_ => Constant::ORDER_STATE_FAILED,
            _PS_OS_PAYMENT_ => Constant::ORDER_STATE_PROCESSING,
            \Configuration::get(OrderManager::WIRECARD_OS_STARTING) => Constant::ORDER_STATE_STARTED,
            \Configuration::get(OrderManager::WIRECARD_OS_AWAITING) => Constant::ORDER_STATE_PENDING,
            \Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION) => Constant::ORDER_STATE_AUTHORIZED,
        ];
    }
}
