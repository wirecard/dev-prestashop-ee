<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Entity;

/**
 * Class EntityBuilderFactory
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity
 * @since 2.4.0
 */
class EntityBuilderFactory
{
    /**
     * @param $entity
     * @throws \Exception
     * @return EntityBuilderInterface
     * @since 2.4.0
     */
    public function create($entity)
    {
        switch ($entity) {
            case EntityBuilderList::BASKET:
                return new BasketBuilder();
            default:
                throw new \Exception('No builder found for this entity' . $entity . '.');
        }
    }
}