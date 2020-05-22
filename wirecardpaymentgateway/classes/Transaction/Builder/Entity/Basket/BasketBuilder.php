<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Builder\Entity\Basket;

use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use WirecardEE\Prestashop\Classes\Transaction\Builder\Entity\EntityBuilderInterface;
use WirecardEE\Prestashop\Classes\Transaction\Entity\Cart\CartItemInterface;
use WirecardEE\Prestashop\Classes\Transaction\Entity\Cart\TransactionCart;

/**
 * Class BasketBuilder
 * @package WirecardEE\Prestashop\Classes\Transaction\Builder\Entity\Basket
 * @since 2.5.0
 */
class BasketBuilder implements EntityBuilderInterface
{
    /**
     * @var TransactionCart
     */
    private $cartData;

    /**
     * BasketBuilder constructor.
     * @param TransactionCart
     * @since 2.5.0
     */
    public function __construct($cartData)
    {
        $this->cartData = $cartData;
    }

    /**
     * @param \Wirecard\PaymentSdk\Transaction\Transaction $transaction
     * @return void|\Wirecard\PaymentSdk\Transaction\Transaction
     * @since 2.5.0
     */
    public function build($transaction)
    {
        $basket = new Basket();
        $basket->setVersion($transaction);

        /** @var CartItemInterface $cartItem */
        foreach ($this->cartData->getCartItems() as $cartItem) {
            $basketItem = new Item(
                $cartItem->getName(),
                $cartItem->getAmount(),
                $cartItem->getQuantity()
            );

            $basketItem->setTaxAmount($cartItem->getTaxAmount());
            $basketItem->setTaxRate($cartItem->getTaxRate());
            $basketItem->setDescription($cartItem->getShortDescription());
            $basketItem->setArticleNumber($cartItem->getProductReference());

            $basket->add($basketItem);
        }

        $transaction->setBasket($basket);

        return $transaction;
    }
}
