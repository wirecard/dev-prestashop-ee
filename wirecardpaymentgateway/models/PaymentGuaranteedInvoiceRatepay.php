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

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
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
    const TYPE = RatepayInvoiceTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentguaranteedinvoiceratepay";

    const MIN_AGE = 18;

    private $currencyHelper;
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

        $this->cancel  = array( 'authorization' );
        $this->capture = array( 'authorization' );
        $this->refund  = array( 'capture-authorization' );

        $this->currencyHelper = new CurrencyHelper();
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
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_ratepayinvoice'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_ratepayinvoice'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->l('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => 'fa02d1d4-f518-4e22-b42b-2abab5867a84',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->l('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => $this->l('config_base_url'),
                    'type'        => 'text',
                    'doc' => $this->l('config_base_url_desc'),
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->l('config_http_user'),
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->l('config_http_password'),
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
                    'label' => $this->l('config_billing_shipping'),
                    'type' => 'onoff',
                    'default' => 1
                ),
                array(
                    'name' => 'shipping_countries',
                    'label' => $this->l('config_shipping_countries'),
                    'type' => 'select',
                    'multiple' => true,
                    'size'=> 10,
                    'default' => array('AT', 'DE', 'CH'),
                    'options'=>'getCountries'
                ),
                array(
                    'name' => 'billing_countries',
                    'label' => $this->l('config_billing_countries'),
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=> 10,
                    'default' => array('AT', 'DE', 'CH'),
                    'options'=>'getCountries'
                ),
                array(
                    'name' => 'allowed_currencies',
                    'label' => $this->l('config_allowed_currencies'),
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=>10,
                    'default' => array('EUR'),
                    'options'=>'getCurrencies'
                ),
                array(
                    'name' => 'amount_min',
                    'label' => $this->l('config_basket_min'),
                    'type' => 'text',
                    'default' => 20,
                    'validator' => 'numeric'
                ),
                array(
                    'name' => 'amount_max',
                    'label' => $this->l('config_basket_max'),
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
                    'label'   => $this->l('config_descriptor'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => $this->l('config_additional_info'),
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->l('test_config'),
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
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $ident = '';
        if (isset($module->context->cookie->wirecardDeviceIdent)) {
            $ident = $module->context->cookie->wirecardDeviceIdent;
            unset($module->context->cookie->wcsConsumerDeviceId);
        }
        $transaction = new RatepayInvoiceTransaction();

        $additionalInformation = new AdditionalInformationBuilder();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));
        $transaction->setOrderNumber($cart->id);
        $device = new Device();
        $transaction->setDevice($device->setFingerPrint($ident));

        return $transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param Transaction $transactionData
     * @return RatepayInvoiceTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $cart = new \Cart($transactionData->cart_id);
        $currency = $transactionData->currency;

        $transaction = new RatepayInvoiceTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setAmount(
            $this->currencyHelper->getAmount(
                $cart->getOrderTotal(),
                $currency
            )
        );

        return $transaction;
    }

    /**
     * Create pay transaction
     *
     * @param Transaction $transactionData
     * @return RatepayInvoiceTransaction
     * @since 1.0.0
     */
    public function createPayTransaction($transactionData)
    {
        $cart = new \Cart($transactionData->cart_id);
        $currency = $transactionData->currency;

        $transaction = new RatepayInvoiceTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        $additionalHelper = new AdditionalInformationBuilder();
        $transaction->setBasket($additionalHelper->createBasket($cart, $transaction, $currency));
        $transaction->setAmount(
            $this->currencyHelper->getAmount(
                $cart->getOrderTotal(),
                $currency
            )
        );

        return $transaction;
    }

    /**
     * Create refund transaction
     *
     * @param Transaction $transactionData
     * @return RatepayInvoiceTransaction
     * @since 1.0.0
     */
    public function createRefundTransaction($transactionData, $module)
    {
        $cart = new \Cart($transactionData->cart_id);
        $currency = $transactionData->currency;

        $transaction = new RatepayInvoiceTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        $additionalHelper = new AdditionalInformationBuilder();
        $transaction->setBasket($additionalHelper->createBasket($cart, $transaction, $currency));
        $transaction->setAmount(
            $this->currencyHelper->getAmount(
                $cart->getOrderTotal(),
                $currency
            )
        );

        return $transaction;
    }

    /**
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @return bool
     * @since 1.0.0
     */
    public function isAvailable($module, $cart)
    {
        /** @var \Customer $customer */
        $customer = new \Customer($cart->id_customer);

        /** @var \Address $billingAddress */
        $billingAddress = new \Address($cart->id_address_invoice);

        /** @var \Address $shippingAddress */
        $shippingAddress = new \Address($cart->id_address_delivery);

        /** @var \Currency $currency */
        $currency = new \Currency($cart->id_currency);

        $birthDay = new \DateTime($customer->birthday);
        $difference = $birthDay->diff(new \DateTime());
        $age = $difference->format('%y');

        if ($cart->isVirtualCart()) {
            return false;
        };

        if ($age < self::MIN_AGE) {
            return false;
        }

        if (!$this->isInLimit($module, $cart->getOrderTotal())) {
            return false;
        }

        if (! $this->isValidAddress($module, $shippingAddress, $billingAddress)) {
            return false;
        }

        if (! in_array($currency->iso_code, $this->getAllowedCurrencies($module))) {
            return false;
        }

        return true;
    }

    /**
     * Check if total amount is in limit minimum and maximum amount
     *
     * @param \WirecardPaymentGateway $module
     * @param float $total
     * @return bool
     * @since 1.0.0
     */
    private function isInLimit($module, $total)
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
     * @param \WirecardPaymentGateway $module
     * @param \Address $shipping
     * @param \Address $billing
     * @return bool
     * @since 1.0.0
     */
    private function isValidAddress($module, $shipping, $billing)
    {
        $isSame = $this->configuration->getField('billingshipping_same');
        if ($isSame && $shipping->id != $billing->id) {
            $fields = array(
                'country',
                'company',
                'firstname',
                'lastname',
                'address1',
                'postcode',
                'city'
            );
            foreach ($fields as $f) {
                if ($billing->$f != $shipping->$f) {
                    return false;
                }
            }
        }

        if (count($this->getAllowedCountries($module, 'shipping'))) {
            $c = new \Country($shipping->id_country);
            if (!in_array($c->iso_code, $this->getAllowedCountries($module, 'shipping'))) {
                return false;
            }
        }

        if (count($this->getAllowedCountries($module, 'billing'))) {
            $c = new \Country($shipping->id_country);
            if (!in_array($c->iso_code, $this->getAllowedCountries($module, 'billing'))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get array with allowed countries per address type
     *
     * @param \WirecardPaymentGateway $module
     * @param string $type
     * @return array
     * @since 1.0.0
     */
    private function getAllowedCountries($module, $type)
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
     * @param \WirecardPaymentGateway $module
     * @return array
     * @since 1.0.0
     */
    private function getAllowedCurrencies($module)
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
}
