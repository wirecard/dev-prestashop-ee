<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Entity;

use WirecardEE\Prestashop\Models\Transaction;

/**
 * Class EntityBuilderFactory
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity
 * @since 2.4.0
 */
class EntityBuilderFactory
{
    /**
     * @var Transaction
     */
    private $parentTransaction;

    /**
     * EntityBuilderFactory constructor.
     * @param Transaction $parentTransaction
     */
    public function __construct($parentTransaction)
    {
        $this->parentTransaction = $parentTransaction;
    }

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
                return $this->initBasket();
            default:
                throw new \Exception('No builder found for this entity' . $entity . '.');
        }
    }

    /**
     * Init BasketBuilder
     *
     * @return BasketBuilder
     * @since 2.4.0
     */
    private function initBasket()
    {
        $cart = new \Cart($this->parentTransaction->getCartId());

        return new BasketBuilder($cart);
    }
}