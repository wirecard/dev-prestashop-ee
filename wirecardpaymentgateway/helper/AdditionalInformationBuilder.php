<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\Transaction;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

/**
 * Class AdditionalInformation
 *
 * @since 1.0.0
 */
class AdditionalInformationBuilder
{
    /** @var int */
    const TAX_RATE_PRECISION = 2;

    /** @var CurrencyHelper */
    private $currencyHelper;

    /** @var int */
    private $roundingPrecision;


    public function __construct()
    {
        $this->currencyHelper = new CurrencyHelper();
        $this->roundingPrecision = \Configuration::get('PS_PRICE_DISPLAY_PRECISION');
    }

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

                if (\Tools::strlen(\Tools::substr(strrchr((string)$grossAmount, '.'), 1)) > 2) {
                    $grossAmount = $product['total_wt'];
                    $name .= ' x' . $quantity;
                    $quantity = 1;
                }

                $netAmount = $product['total'] / $quantity;
                $taxAmount = $grossAmount - $netAmount;
                $taxRate = \Tools::ps_round($taxAmount / $grossAmount * 100, $this->roundingPrecision);

                $amount = $this->currencyHelper->getAmount(
                    \Tools::ps_round($grossAmount, $this->roundingPrecision),
                    $currency
                );

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
     * @param \Cart $cart
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
        $transaction->setAccountHolder($this->createAccountHolder($cart, 'billing', $firstName, $lastName));

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
     * @param \Cart $cart
     * @param string $type
     * @param string|null $firstName
     * @param string|null $lastName
     * @return AccountHolder
     * @since 1.0.0
     */
    public function createAccountHolder($cart, $type, $firstName = null, $lastName = null)
    {
        $customer = new \Customer($cart->id_customer);
        $addressId = ('shipping' === $type)
            ? $cart->id_address_delivery
            : $cart->id_address_invoice;

        $address = new \Address($addressId);
        $customerFirstName = $firstName ?: $address->firstname;
        $customerLastName = $lastName ?: $address->lastname;
        $customerPhone = trim($address->phone);
        $customerPhoneMobile = trim($address->phone_mobile);

        $accountHolder = new AccountHolder();
        $accountHolder->setAddress($this->createAddressData($address, $type));
        $accountHolder->setEmail($customer->email);
        $accountHolder->setFirstName($customerFirstName);
        $accountHolder->setLastName($customerLastName);

        if (\Tools::strlen($customerPhone)) {
            $accountHolder->setPhone($customerPhone);
        } else if (\Tools::strlen($customerPhoneMobile)) {
            $accountHolder->setPhone($customerPhoneMobile);
        }

        if ($type === 'billing') {
            if (\Tools::strlen($customerPhoneMobile)) {
                $accountHolder->setMobilePhone($customerPhoneMobile);
            }
        }

        if (isset($customer->birthday) &&
            $customer->birthday !== '0000-00-00' &&
            $type === 'billing'
        ) {
            $accountHolder->setDateOfBirth(new \DateTime($customer->birthday));
        }

        return $accountHolder;
    }

    /**
     * Create addressdata for shipping or billing
     *
     * @param \Address $source
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
     * @param \Country $country
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
