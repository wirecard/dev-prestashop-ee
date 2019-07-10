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
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\Transaction;

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
     * @param \Cart $cart
     * @param Transaction $transaction
     * @param string $currency
     * @return Basket
     * @since 1.0.0
     */
    public function createBasket($cart, $transaction, $currency)
    {
        $basket = new Basket();
        $basket->setVersion($transaction);

        if (!empty($cart->getProducts())) {
            foreach ($cart->getProducts() as $product) {
                $quantity = $product['cart_quantity'];
                $name = \Tools::substr($product['name'], 0, 127);
                $grossAmount = $product['total_wt'] / $quantity;

                //Check for rounding issues
                if (\Tools::strlen(\Tools::substr(strrchr((string)$grossAmount, '.'), 1)) > 2) {
                    $grossAmount = $product['total_wt'];
                    $name .= ' x' . $quantity;
                    $quantity = 1;
                }

                $netAmount = $product['total'] / $quantity;
                $taxAmount = $grossAmount - $netAmount;
                $taxRate = number_format($taxAmount / $grossAmount * 100, 2);
                $amount = new Amount((float) number_format($grossAmount, 2, '.', ''), $currency);

                $item = new Item($name, $amount, $quantity);
                $item->setDescription(\Tools::substr(strip_tags($product['description_short']), 0, 127));
                $item->setArticleNumber($product['reference']);
                $item->setTaxRate($taxRate);

                $basket->add($item);
            }
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
     * Create shop descriptor
     *
     * @param string $id
     * @return string
     * @since 1.0.0
     */
    public function createDescriptor($id)
    {
        $shopName = preg_replace('/[^a-zA-Z0-9]/', '', \Configuration::get('PS_SHOP_NAME'));

        return sprintf(
            '%s %s',
            \Tools::substr($shopName, 0, 9),
            $id
        );
    }

    /**
     * Create additional information for fps
     *
     * @param Cart $cart
     * @param string $id
     * @param Transaction $transaction
     * @param string $currency
     * @param string $firstName (optional)
     * @param string $lastName (optional)
     * @return Transaction
     * @since 1.0.0
     */
    public function createAdditionalInformation(
        $cart,
        $id,
        $transaction,
        $currency,
        $firstName = null,
        $lastName = null
    ) {
        $transaction->setDescriptor($this->createDescriptor($id));

        if ($lastName) {
            $transaction->setAccountHolder($this->createCreditCardAccountHolder($cart, $firstName, $lastName));
        } else {
            $transaction->setAccountHolder($this->createAccountHolder($cart, 'billing'));
        }

        $transaction->setShipping($this->createAccountHolder($cart, 'shipping'));
        $transaction->setOrderNumber($id);
        $transaction->setBasket($this->createBasket($cart, $transaction, $currency));
        $transaction->setIpAddress($this->getConsumerIpAddress());
        $transaction->setConsumerId($cart->id_customer);

        return $transaction;
    }

    /**
     * Create accountholder for shipping or billing
     *
     * @param Cart $cart
     * @param string $type
     * @return AccountHolder
     * @since 1.0.0
     */
    public function createAccountHolder($cart, $type)
    {
        $customer = new \Customer($cart->id_customer);
        $billing = new \Address($cart->id_address_invoice);
        $shipping = new \Address($cart->id_address_delivery);

        $accountHolder = new AccountHolder();
        if ('shipping' == $type) {
            $accountHolder->setAddress($this->createAddressData($shipping, $type));
            $accountHolder->setFirstName($shipping->firstname);
            $accountHolder->setLastName($shipping->lastname);
        } else {
            $accountHolder->setAddress($this->createAddressData($billing, $type));
            $accountHolder->setEmail($customer->email);
            $accountHolder->setFirstName($billing->firstname);
            $accountHolder->setLastName($billing->lastname);
            $accountHolder->setPhone($billing->phone);
            if (isset($customer->birthday) && $customer->birthday !== '0000-00-00') {
                $accountHolder->setDateOfBirth(new \DateTime($customer->birthday));
            }
        }

        return $accountHolder;
    }

    /**
     * Create accountholder for creditcard transaction
     *
     * @param Cart $cart
     * @param string $firstName
     * @param string $lastName
     * @return AccountHolder
     * @since 1.3.4
     */
    public function createCreditCardAccountHolder($cart, $firstName, $lastName)
    {
        $customer = new \Customer($cart->id_customer);
        $billing = new \Address($cart->id_address_invoice);

        $accountHolder = new AccountHolder();

        $accountHolder->setAddress($this->createAddressData($billing, 'billing'));
        $accountHolder->setEmail($customer->email);
        if ($firstName) {
            $accountHolder->setFirstName($firstName);
        }
        $accountHolder->setLastName($lastName);
        $accountHolder->setPhone($billing->phone);
        if (isset($customer->birthday) && $customer->birthday !== '0000-00-00') {
            $accountHolder->setDateOfBirth(new \DateTime($customer->birthday));
        }

        return $accountHolder;
    }

    /**
     * Create addressdata for shipping or billing
     *
     * @param PrestaShop\Address $source
     * @param string $type
     * @return Address
     * @since 1.0.0
     */
    public function createAddressData($source, $type)
    {
        $country = new \Country($source->id_country);

        $state = (new \State($source->id_state))->iso_code;
        $state = $this->sanitizeState($country, $state);

        $address = new Address($country->iso_code, $source->city, $source->address1);
        $address->setPostalCode($source->postcode);

        if (\Tools::strlen($source->address2)) {
            $address->setStreet2($source->address2);
        }

        if (\Tools::strlen($state)) {
            $address->setState($state);
        }

        return $address;
    }

    /**
     * Create consumer ip address
     *
     * @return string
     * @since 1.0.0
     */
    public function getConsumerIpAddress()
    {
        if (!method_exists('Tools', 'getRemoteAddr')) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR']) {
                if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                    return $ips[0];
                } else {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            }

            return $_SERVER['REMOTE_ADDR'];
        } else {
            return \Tools::getRemoteAddr();
        }
    }

    /**
     * Sanitizes the state before conversion.
     *
     * @param Prestashop\Country $country
     * @param string $state
     * @return string
     * @since 1.2.0
     */
    private function sanitizeState($country, $state)
    {
        // The only diversion from ISO 3166 so we can safely/reasonably do this.
        if ($country->iso_code === 'ID' && \Tools::strlen($state)) {
            $state = str_replace('ID-', '', $state);
        }

        return $state;
    }
}
