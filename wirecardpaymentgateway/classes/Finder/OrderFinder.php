<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Finder;

use Order;

/**
 * Class OrderFinder
 * @package WirecardEE\Prestashop\Classes\Finder
 */
class OrderFinder extends DbFinder
{

    /**
     * @param int $orderId
     * @return Order
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderById($orderId)
    {
        return new Order($orderId);
    }

    /**
     * @param string $reference
     * @return \ObjectModel|Order
     */
    public function getOrderByReference($reference)
    {
        return (Order::getByReference($reference))->getFirst();
    }
}
