<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Adapter\Cart;

use Wirecard\PaymentSdk\Entity\Amount;
use WirecardEE\Prestashop\Classes\Transaction\Adapter\Product\CartItem;
use WirecardEE\Prestashop\Classes\Transaction\Adapter\Product\CartItemCollection;

/**
 * Class TransactionCartData
 * @package WirecardEE\Prestashop\Classes\Transaction\Adapter\Cart
 * @since 2.4.0
 */
class TransactionCartData implements CartDataInterface
{
    /**
     * @var CartItemCollection
     * @since 2.4.0
     */
    private $cartItemCollection;

    /**
     * TransactionCartData constructor.
     * @param string $transactionRawData
     * @since 2.4.0
     */
    public function __construct($transactionRawData)
    {
        $this->cartItemCollection = new CartItemCollection();
        $this->createProductCollection($transactionRawData);
    }

    /**
     * @return void|CartItemCollection
     * @since 2.4.0
     */
    public function getCartItems()
    {
        return $this->cartItemCollection;
    }

    /**
     * @param string $transactionRawData
     * @since 2.4.0
     */
    private function createProductCollection($transactionRawData)
    {
        /** @var array $basket */
        $basket = $this->parseToArrayItems($transactionRawData);

        foreach ($basket as $product) {
            $productData = new CartItem(
                $product['name'],
                new Amount(
                    \Tools::ps_round($product['amount'], \Configuration::get('PS_PRICE_DISPLAY_PRECISION')),
                    $product['currency']
                ),
                $product['quantity'],
                $product['description'],
                $product['article_number']
            );

            if (isset($product['tax_rate'])) {
                $productData->setTaxRate(
                    \Tools::ps_round($product['tax_rate'], \Configuration::get('PS_PRICE_DISPLAY_PRECISION'))
                );
            }

            if (isset($product['tax_amount'])) {
                $productData->setTaxAmount(
                    new Amount(
                        \Tools::ps_round($product['tax_amount']),
                        $product['currency']
                    )
                );
            }

            $this->cartItemCollection[] = $productData;
        }
    }

    /**
     * @param string $transactionRawData
     * @return array
     * @since 2.4.0
     */
    private function parseToArrayItems($transactionRawData)
    {
        $basket = [];
        foreach (json_decode($transactionRawData, true) as $key => $value) {
            if (strpos($key, 'order-items') !== false) {
                $item_start = substr($key, strpos($key, 'order-item.') + strlen('order-item.'));
                $item_count = substr($item_start, 0, strpos($item_start, '.'));
                $item_name = substr($item_start, strpos($item_start, '.') + 1);
                $item_name = str_replace('-', '_', $item_name);

                $basket[$item_count][$item_name] = $value;
            }

        }

        return $basket;
    }
}
