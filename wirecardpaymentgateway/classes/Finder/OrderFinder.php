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

namespace WirecardEE\Prestashop\Classes\Finder;

/**
 * Class OrderFinder
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Finder
 */
class OrderFinder extends DbFinder
{

    /**
     * @param int $orderId
     * @return Order
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @since 2.5.0
     */
    public function getOrderById($orderId)
    {
        // @TODO: Please use this method in next releases instead of pure SQL query.
        return new \Order($orderId);
    }

    /**
     * @param string $reference
     * @return \ObjectModel|\Order
     * @since 2.5.0
     */
    public function getOrderByReference($reference)
    {
        return (\Order::getByReference($reference))->getFirst();
    }
}
