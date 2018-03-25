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

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

/**
 * Class PaymentPaypal
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentGuaranteedInvoiceRatepay extends Payment
{

    /**
     * PaymentPaypal constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type = 'invoice';
        $this->name = 'Wirecard Payment Processing Gateway Guaranteed Invoice';
        $this->formFields = $this->createFormFields();

        $this->cancel  = array( 'authorization' );
        $this->capture = array( 'authorization' );
        $this->refund  = array( 'debit', 'capture-authorization' );
    }

    /**
     * Create form fields for paypal
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
                    'label' => 'Enable',
                    'type' => 'onoff',
                    'doc' => 'Enable Wirecard Payment Processing Gateway Guaranteed Invoice',
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Wirecard Payment Processing Gateway Invoice',
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => 'Merchant Account ID',
                    'type'    => 'text',
                    'default' => 'fa02d1d4-f518-4e22-b42b-2abab5867a84',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => 'Secret key',
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => 'Base url',
                    'type'        => 'text',
                    'doc' => 'The elastic engine base url. (e.g. https://api.wirecard.com)',
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => 'Http user',
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => 'Http password',
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'billingshipping_same',
                    'label' => 'Billing/Shipping address must be identical',
                    'type' => 'onoff',
                    'default' => 1
                ),
                array(
                    'name' => 'shipping_countries',
                    'label' => 'Allowed shipping countries',
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=>10,
                    'default' => array('AT', 'DE', 'CH'),
                    'options'=>'getCountries'
                ),
                array(
                    'name' => 'billing_countries',
                    'label' => 'Allowed billing countries',
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=>10,
                    'default' => array('AT', 'DE', 'CH'),
                    'options'=>'getCountries'
                ),
                array(
                    'name' => 'allowed_currencies',
                    'label' => 'Allowed currencies',
                    'type' => 'select',
                    'multiple'=>true,
                    'size'=>10,
                    'default' => array('EUR'),
                    'options'=>'getCurrencies'
                ),
                array(
                    'name' => 'amount_min',
                    'label' => 'Minimum amount',
                    'type' => 'text',
                    'default' => 20,
                    'validator' => 'numeric'
                ),
                array(
                    'name' => 'amount_max',
                    'label' => 'Maximum amount',
                    'type' => 'text',
                    'default' => 3500,
                    'validator' => 'numeric'
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => 'Send additional information',
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => 'Test configuration',
                    'id' => 'paypalConfig',
                    'method' => 'paypal',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_PAYPAL_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_PAYPAL_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_PAYPAL_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for ratepay invoice transactions
     *
     * @param \WirecardPaymentGateway $paymentModule
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 1.0.0
     */
    public function createPaymentConfig($paymentModule)
    {
        $baseUrl  = $paymentModule->getConfigValue($this->type, 'base_url');
        $httpUser = $paymentModule->getConfigValue($this->type, 'http_user');
        $httpPass = $paymentModule->getConfigValue($this->type, 'http_pass');

        $merchantAccountId = $paymentModule->getConfigValue($this->type, 'merchant_account_id');
        $secret = $paymentModule->getConfigValue($this->type, 'secret');

        $config = $this->createConfig($baseUrl, $httpUser, $httpPass);
        $paymentConfig = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, $merchantAccountId, $secret);
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create RatepayInvoiceTransaction
     *
     * @return RatepayInvoiceTransaction
     * @since 1.0.0
     */
    public function createTransaction()
    {
        $transaction = new RatepayInvoiceTransaction();

        return $transaction;
    }
}
