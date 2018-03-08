<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\Transaction;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;

/**
 * Class AdditionalInformation
 *
 * @since 1.0.0
 */
class AdditionalInformation
{
    /**
     * Create basket items for transaction
     *
     * @param $cart
     * @param Transaction $transaction
     * @param string $currency
     * @return Basket
     * @since 1.0.0
     */
    public function createBasket($cart, $transaction, $currency)
    {
        $basket = new Basket();
        $basket->setVersion($transaction);

        foreach ($cart->getProducts() as $product) {
            $quantity = $product['cart_quantity'];
            $name = Tools::substr($product['name'], 0, 127);
            $grossAmount = $product['total_wt'] / $quantity;

            //Check for rounding issues
            if (Tools::strlen(Tools::substr(strrchr((string)$grossAmount, '.'), 1)) > 2) {
                $grossAmount = $product['total_wt'];
                $name .= ' x' . $quantity;
                $quantity = 1;
            }

            $netAmount = $product['total'] / $quantity;
            $taxAmount = $grossAmount - $netAmount;
            $taxRate = number_format($taxAmount / $grossAmount * 100, 2);
            $amount = new Amount(number_format($grossAmount, 2, '.', ''), $currency);

            $item = new Item($name, $amount, $quantity);
            $item->setDescription(Tools::substr(strip_tags($product['description_short']), 0, 127));
            $item->setArticleNumber($product['reference']);
            $item->setTaxRate($taxRate);

            $basket->add($item);
        }

        if ($cart->getTotalShippingCost(null, true) > 0) {
            $grossAmount = $cart->getTotalShippingCost(null, true);
            $netAmount = $cart->getTotalShippingCost(null, false);
            $taxRate = ( $grossAmount / $netAmount -1 ) * 100;

            $item = new Item('Shipping', new Amount($grossAmount, $currency), 1);
            $item->setDescription('Shipping');
            $item->setArticleNumber('Shipping');
            $item->setTaxRate($taxRate);

            $basket->add($item);
        }

        return $basket;
    }

    /**
     * Default create descriptor
     *
     * @param $order
     * @return string
     * @since 1.0.0
     */
    public function createDescriptor( $order ) {
        return sprintf(
            '%s %s',
            substr( 'name' , 0, 9 ),
            '1'
        );
    }

    public function setAdditionalInformation( $cart, $order, $transaction, $currency ) {
        $transaction->setDescriptor( $this->createDescriptor( $order ) );
        $transaction->setAccountHolder( $this->createAccountHolder( $order, 'billing' ) );
        $transaction->setShipping( $this->createAccountHolder( $order, 'shipping' ) );
        $transaction->setOrderNumber( 'orderNumber' );
        $transaction->setBasket( $this->createBasket( $cart, $transaction, $currency ) );
        $transaction->setIpAddress( 'customerIpAddress' );
        $transaction->setConsumerId( 'customerId' );

        return $transaction;
    }

    /**
     * @param $order
     * @param $type
     * @return AccountHolder
     */
    public function creatAccountHolder( $order, $type ) {
        $accountHolder = new AccountHolder();
        if ( self::SHIPPING == $type ) {
            $accountHolder->setAddress( $this->createAddressData( $order, $type ) );
            $accountHolder->setFirstName( 'name' );
            $accountHolder->setLastName('name' );
        } else {
            $accountHolder->setAddress( $this->createAddressData( $order, $type ) );
            $accountHolder->setEmail( 'b' );
            $accountHolder->setFirstName( 'name' );
            $accountHolder->setLastName( 'name' );
            $accountHolder->setPhone( 'phonhe' );
        }

        return $accountHolder;
    }

    /**
     * @param $order
     * @param $type
     * @return Address
     */
    public function createAddressData( $order, $type ) {
        if ( self::SHIPPING == $type ) {
            $address = new Address( 'country', 'city', 'address' );
            $address->setPostalCode( 'postcode' );
        } else {
            $address = new Address(  'country', 'city', 'address'  );
            $address->setPostalCode( 'postcode' );
            if ( strlen( 'address2' ) ) {
                $address->setStreet2( 'address2' );
            }
        }

        return $address;
    }
}
