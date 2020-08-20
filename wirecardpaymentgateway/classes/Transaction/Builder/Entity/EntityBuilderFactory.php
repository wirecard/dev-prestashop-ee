<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Builder\Entity;

use WirecardEE\Prestashop\Classes\Transaction\Builder\Entity\Basket\BasketBuilder;
use WirecardEE\Prestashop\Classes\Transaction\Entity\Cart\TransactionCart;
use WirecardEE\Prestashop\Models\Transaction;

/**
 * Class EntityBuilderFactory
 * @package WirecardEE\Prestashop\Classes\Transaction\Builder\Entity
 * @since 2.5.0
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
     * @since 2.5.0
     */
    public function __construct($parentTransaction)
    {
        $this->parentTransaction = $parentTransaction;
    }

    /**
     * @param string $entity
     * @throws \Exception
     * @return EntityBuilderInterface
     * @since 2.5.0
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
     * @since 2.5.0
     */
    private function initBasket()
    {
        $basketData = new TransactionCart($this->parentTransaction->getResponse());

        return new BasketBuilder($basketData);
    }
}
