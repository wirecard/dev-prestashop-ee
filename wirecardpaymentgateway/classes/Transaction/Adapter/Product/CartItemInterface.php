<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Adapter\Product;

use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Interface CartItemInterface
 * @package WirecardEE\Prestashop\Classes\Transaction\Adapter\Product
 * @since 2.4.0
 */
interface CartItemInterface
{
    /**
     * @return string
     * @since 2.4.0
     */
    public function getName();

    /**
     * @return Amount
     * @since 2.4.0
     */
    public function getAmount();

    /**
     * @return int
     * @since 2.4.0
     */
    public function getQuantity();

    /**
     * @return string
     * @since 2.4.0
     */
    public function getShortDescription();

    /**
     * @return string
     * @since 2.4.0
     */
    public function getProductReference();

    /**
     * @return Amount
     * @since 2.4.0
     */
    public function getTaxAmount();

    /**
     * @return float
     * @since 2.4.0
     */
    public function getTaxRate();
}
