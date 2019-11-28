<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use WirecardEE\Prestashop\Classes\Transaction\Builder\Entity\EntityBuilderList;
use WirecardEE\Prestashop\Helper\AdditionalInformationBuilder;
use WirecardEE\Prestashop\Helper\CurrencyHelper;
use WirecardEE\Prestashop\Helper\DeviceIdentificationHelper;

/**
 * Class PaymentGuaranteedInvoiceRatepay
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentGuaranteedInvoiceRatepay extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = RatepayInvoiceTransaction::PAYMENT_NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentguaranteedinvoiceratepay";

    const MIN_AGE = 18;

    /**
     * PaymentGuaranteedInvoiceRatepay constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard Guaranteed Invoice';
        $this->formFields = $this->createFormFields();
        $this->setLoadJs(true);
    }

    /**
     * Create form fields for invoice
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'Invoice',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_ratepayinvoice'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_ratepayinvoice'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => 'fa02d1d4-f518-4e22-b42b-2abab5867a84',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => $this->getTranslatedString('config_base_url'),
                    'type'        => 'text',
                    'doc' => $this->getTranslatedString('config_base_url_desc'),
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->getTranslatedString('config_http_user'),
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->getTranslatedString('config_http_password'),
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'hidden',
                    'default' => 'reserve',
                ),
                array(
                    'name' => 'billingshipping_same',
                    'label' => $this->getTranslatedString('config_billing_shipping'),
                    'type' => 'onoff',
                    'default' => 1
                ),
                array(
                    'name' => 'shipping_countries',
                    'label' => $this->getTranslatedString('config_shipping_countries'),
                    'type' => 'select',
                    'multiple' => true,
                    'size'=> 10,
                    'default' => array('AT', 'DE', 'CH'),
                    'options'=>'getCountries'
                ),
                array(
                    'name' => 'billing_countries',
                    'label' => $this->getTranslatedString('config_billing_countries'),
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=> 10,
                    'default' => array('AT', 'DE', 'CH'),
                    'options'=>'getCountries'
                ),
                array(
                    'name' => 'allowed_currencies',
                    'label' => $this->getTranslatedString('config_allowed_currencies'),
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=>10,
                    'default' => array('EUR'),
                    'options'=>'getCurrencies'
                ),
                array(
                    'name' => 'amount_min',
                    'label' => $this->getTranslatedString('config_basket_min'),
                    'type' => 'text',
                    'default' => 20,
                    'validator' => 'numeric'
                ),
                array(
                    'name' => 'amount_max',
                    'label' => $this->getTranslatedString('config_basket_max'),
                    'type' => 'text',
                    'default' => 3500,
                    'validator' => 'numeric'
                ),
                array(
                    'name' => 'shopping_basket',
                    'type'    => 'hidden',
                    'default' => 1,
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => $this->getTranslatedString('config_descriptor'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => $this->getTranslatedString('config_additional_info'),
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->getTranslatedString('test_config'),
                    'id' => 'invoiceConfig',
                    'method' => 'invoice',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_INVOICE_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_INVOICE_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_INVOICE_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create Ratepay invoice transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|RatepayInvoiceTransaction
     * @since 1.0.0
     */
    public function createTransaction($operation = null)
    {
        $context = \Context::getContext();
        $cart = $context->cart;
        $ident = '';

        if (isset($context->cookie->wirecardDeviceIdent)) {
            $ident = $context->cookie->wirecardDeviceIdent;
            unset($context->cookie->wcsConsumerDeviceId);
        }

        $transaction = $this->createTransactionInstance($operation);

        $additionalInformation = new AdditionalInformationBuilder();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));
        $transaction->setOrderNumber($cart->id);

        $device = new Device();
        $transaction->setDevice($device->setFingerPrint($ident));

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @param string $operation
     * @return RatepayInvoiceTransaction
     * @since 2.4.0
     */
    public function createTransactionInstance($operation = null)
    {
        return new RatepayInvoiceTransaction();
    }

    /**
     * @param \Cart $cart
     * @throws \Exception
     * @return bool
     * @since 1.0.0
     */
    public function isAvailable()
    {
        $cart = $this->getCartFromContext();

        /** @var \Customer $customer */
        $customer = new \Customer($cart->id_customer);

        /** @var \Address $billingAddress */
        $billingAddress = new \Address($cart->id_address_invoice);

        /** @var \Address $shippingAddress */
        $shippingAddress = new \Address($cart->id_address_delivery);

        /** @var \Currency $currency */
        $currency = new \Currency($cart->id_currency);

        if ($cart->isVirtualCart() ||
            !$this->isAboveAgeLimit($customer->birthday) ||
            !$this->isInLimit($cart->getOrderTotal()) ||
            !$this->isValidAddress($shippingAddress, $billingAddress) ||
            !in_array($currency->iso_code, $this->getAllowedCurrencies())
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if total amount is in limit minimum and maximum amount
     *
     * @param float $total
     * @return bool
     * @since 1.0.0
     */
    private function isInLimit($total)
    {
        $currencyConverter = new CurrencyHelper();
        $currency = \Context::getContext()->currency;

        $minimum = $currencyConverter->convertToCurrency(
            $this->configuration->getField('amount_min'),
            $currency->iso_code
        );

        $maximum = $currencyConverter->convertToCurrency(
            $this->configuration->getField('amount_max'),
            $currency->iso_code
        );

        if ($minimum > $total || $total > $maximum) {
            return false;
        }

        return true;
    }

    /**
     * Validate address information (shipping, billing)
     *
     * @param \Address $shippingAddress
     * @param \Address $billingAddress
     * @return bool
     * @since 1.0.0
     */
    private function isValidAddress($shippingAddress, $billingAddress)
    {
        $isSame = $this->configuration->getField('billingshipping_same');
        //@TODO refactor this complicated block
        if ($isSame && $shippingAddress->id != $billingAddress->id) {
            $fieldsToCompare = array(
                'country',
                'company',
                'firstname',
                'lastname',
                'address1',
                'postcode',
                'city'
            );
            foreach ($fieldsToCompare as $field) {
                if ($billingAddress->$field != $shippingAddress->$field) {
                    return false;
                }
            }
        }

        if (!$this->isCountryAllowed($shippingAddress, 'shipping') ||
            !$this->isCountryAllowed($billingAddress, 'billing')) {
            return false;
        }

        return true;
    }

    /**
     * Get array with allowed countries per address type
     *
     * @param string $type
     * @return array
     * @since 1.0.0
     */
    private function getAllowedCountries($type)
    {
        $val = $this->configuration->getField($type . '_countries');
        if (!\Tools::strlen($val)) {
            return array();
        }

        $countries = \Tools::jsonDecode($val);
        if (!is_array($countries)) {
            return array();
        }

        return $countries;
    }

    /**
     * Get array with allowed currencies
     *
     * @return array
     * @since 1.0.0
     */
    private function getAllowedCurrencies()
    {
        $val = $this->configuration->getField('allowed_currencies');

        if (!\Tools::strlen($val)) {
            return array();
        }

        $currencies = \Tools::jsonDecode($val);
        if (!is_array($currencies)) {
            return array();
        }

        return $currencies;
    }

    protected function getFormTemplateData()
    {
        return array(
          'device_identification' => DeviceIdentificationHelper::generateFingerprint()
        );
    }

    /**
     * @param string $birthDate
     * @param int $ageLimit
     * @return bool
     * @throws \Exception
     *
     * @since 2.5.0
     */
    private function isAboveAgeLimit($birthDate, $ageLimit = self::MIN_AGE)
    {
        $birthDay = new \DateTime($birthDate);
        $difference = $birthDay->diff(new \DateTime());
        $age = $difference->format('%y');

        return $age > $ageLimit;
    }

    /**
     * Return Guaranteed Invoice Ratepay post processing mandatory entities
     *
     * @return array
     * @since 2.4.0
     */
    public function getPostProcessingMandatoryEntities()
    {
        return [
            EntityBuilderList::BASKET
        ];
    }

    /**
     * @return \Cart
     * @since 2.5.0
     */
    protected function getCartFromContext()
    {
        return (\Context::getContext())->cart;
    }

    /**
     * Checks if the address country is valid for the merchant configuration
     * as $type you can set 'shipping' or 'billing'
     *
     * @param \Address $address
     * @param string $type
     * @return bool
     * @since 2.5.0
     */
    private function isCountryAllowed($address, $type)
    {
        $configuredCountries = $this->getAllowedCountries($type);
        //if empty no countries are allowed
        if (empty($configuredCountries)) {
            return false;
        }

        $addressCountry = new \Country($address->id_country);
        if (!in_array($addressCountry->iso_code, $configuredCountries)) {
            return false;
        }

        return true;
    }
}
