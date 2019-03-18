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

use Wirecard\PaymentSdk\Transaction\PaylibTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use WirecardEE\Prestashop\Helper\AdditionalInformation;

/**
 * Class PaymentPaylib
 *
 * @extends Payment
 *
 * @since 1.4.0
 */
class PaymentPaylib extends Payment
{

    /**
     * PaymentPaylib constructor.
     *
     * @since 1.4.0
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->type = 'paylib';
        $this->name = 'Wirecard Paylib';
        $this->formFields = $this->createFormFields();
    }

    /**
     * Create form fields for Paylib
     *
     * @return array|null
     * @since 1.4.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'Paylib',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_paylib'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_paylib'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label' => $this->l('config_merchant_account_id'),
                    'type' => 'text',
                    'default' => 'f5f399c1-78b5-4559-bc0c-e077cb686ca9',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label' => $this->l('config_merchant_secret'),
                    'type' => 'text',
                    'default' => 'NO-SECRET-PROVIDED',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label' => $this->l('config_base_url'),
                    'type' => 'text',
                    'doc' => $this->l('config_base_url_desc'),
                    'default' => 'https://test-paylib.free.beeceptor.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label' => $this->l('config_http_user'),
                    'type' => 'text',
                    'default' => 'HTTP-USER',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label' => $this->l('config_http_password'),
                    'type' => 'text',
                    'default' => 'HTTP-PASSWORD',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type' => 'hidden',
                    'default' => 'pay',
                ),
                array(
                    'name' => 'descriptor',
                    'label' => $this->l('config_descriptor'),
                    'type' => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label' => $this->l('config_additional_info'),
                    'type' => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->l('test_config'),
                    'id' => 'paylibConfig',
                    'method' => 'paylib',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_PAYLIB_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_PAYLIB_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_PAYLIB_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for Paylib transactions
     *
     * @param \WirecardPaymentGateway $module
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 1.4.0
     */
    public function createPaymentConfig($module)
    {
        $baseUrl  = $module->getConfigValue($this->type, 'base_url');
        $httpUser = $module->getConfigValue($this->type, 'http_user');
        $httpPass = $module->getConfigValue($this->type, 'http_pass');

        $merchantAccountId = $module->getConfigValue($this->type, 'merchant_account_id');
        $secret = $module->getConfigValue($this->type, 'secret');

        $config = $this->createConfig($baseUrl, $httpUser, $httpPass);
        $paymentConfig = new PaymentMethodConfig(PaylibTransaction::NAME, $merchantAccountId, $secret);
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create default Paylib transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|PaylibTransaction
     * @since 1.4.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new PaylibTransaction();

        $additionalInformation = new AdditionalInformation();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));

        return $transaction;
    }
}
