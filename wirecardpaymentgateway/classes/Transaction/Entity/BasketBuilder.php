<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Entity;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;

/**
 * Class BasketBuilder
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity
 * @since 2.4.0
 */
class BasketBuilder implements EntityBuilderInterface
{
    /**
     * @var \Cart
     */
    private $cart;

    /**
     * BasketBuilder constructor.
     * @param \Cart $cart
     * @since 2.4.0
     */
    public function __construct($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @param \Wirecard\PaymentSdk\Transaction\Transaction $transaction
     * @param \WirecardEE\Prestashop\Models\Transaction $parentTransactionData
     * @return void|\Wirecard\PaymentSdk\Transaction\Transaction
     * @since 2.4.0
     */
    public function build($transaction)
    {
        $order_id = \Order::getIdByCartId($this->cart->id);

        $basket = new Basket();
        $basket->setVersion($transaction);

        /** @var \Currency $currency */
        $currency = new \Currency($this->cart->id_currency);

        $context = \Context::getContext();

        /** @var array $product */
        foreach ($this->cart->getProducts() as $product) {
            var_dump("product");
            $grossAmount = $product['total_wt'] / $product['cart_quantity'];
            $nettoAmount = $product['price_with_reduction_without_tax'];
            $taxAmount = $grossAmount - $nettoAmount;

            //@TODO here we need to do currency conversion.

            $basket->add(
                $this->createItem(
                    \Tools::substr($product['name'], 0, 127),
                    (new Amount($grossAmount, $currency->iso_code)),
                    $product['quantity'],
                    \Tools::substr(strip_tags($product['description_short']), 0, 127),
                    $product['reference'],
                    (new Amount($taxAmount, $currency->iso_code)),
                    $product['rate']
                )
            );
        }
        die();
        $transaction->setBasket($basket);

        return $transaction;
    }

    /**
     * @param string $name
     * @param Amount $amount
     * @param int $quantity
     * @param sting $shortDescription
     * @param $articleNumber
     * @param $taxAmount
     * @param $taxRate
     * @return Item
     */
    private function createItem(
        $name,
        $amount,
        $quantity,
        $shortDescription,
        $articleNumber,
        $taxAmount,
        $taxRate
    ) {
        $item = new Item($name, $amount, $quantity);

        $item->setArticleNumber($articleNumber)
            ->setDescription($shortDescription)
            ->setTaxRate($taxRate)
            ->setTaxAmount($taxAmount);

        return $item;
    }
}
