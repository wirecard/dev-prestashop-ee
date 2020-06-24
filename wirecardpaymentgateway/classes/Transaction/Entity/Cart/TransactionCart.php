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

namespace WirecardEE\Prestashop\Classes\Transaction\Entity\Cart;

/**
 * Class TransactionCartData
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity\Cart
 * @since 2.5.0
 */
class TransactionCart implements CartInterface
{
    /**
     * @var CartItemCollection
     * @since 2.5.0
     */
    private $cartItemCollection;

    /**
     * TransactionCartData constructor.
     * @param string $transactionRawData
     * @since 2.5.0
     */
    public function __construct($transactionRawData)
    {
        $this->cartItemCollection = new CartItemCollection();
        $this->cartItemCollection->createProductCollectionFromTransactionData($transactionRawData);
    }

    /**
     * @return CartItemCollection
     * @since 2.5.0
     */
    public function getCartItems()
    {
        return $this->cartItemCollection;
    }
}
