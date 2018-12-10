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

use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use WirecardEE\Prestashop\Helper\AdditionalInformation;

/**
 * Class PaymentAlipayCrossborder
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentAlipayCrossborder extends Payment
{

    /**
     * PaymentAlipayCrossborder constructor.
     *
     * @since 1.0.0
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->type = 'alipay-xborder';
        $this->name = 'Wirecard Alipay Crossborder';
        $this->formFields = $this->createFormFields();

        $this->refund  = array( 'debit');
    }

    /**
     * Create form fields for alipay
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'Alipay-XBorder',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_alipay_crossboarder'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_alipay_crossborder'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->l('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '7ca48aa0-ab12-4560-ab4a-af1c477cce43',
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
                    'default' => 'pay',
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
                    'id' => 'alipaycrossborderConfig',
                    'method' => 'alipay-xborder',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_ALIPAY-XBORDER_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_ALIPAY-XBORDER_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_ALIPAY-XBORDER_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for alipay crossborder transactions
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
        $paymentConfig = new PaymentMethodConfig(AlipayCrossborderTransaction::NAME, $merchantAccountId, $secret);
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create Alipay Crossborder transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|AlipayCrossborderTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new AlipayCrossborderTransaction();

        $additionalInformation = new AdditionalInformation();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));

        return $transaction;
    }

    /**
     * Create refund transaction
     *
     * @param Transaction $transactionData
     * @return AlipayCrossborderTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new AlipayCrossborderTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setAmount(new Amount($transactionData->amount, $transactionData->currency));

        return $transaction;
    }
}
