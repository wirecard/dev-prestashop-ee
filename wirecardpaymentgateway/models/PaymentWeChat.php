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

use Wirecard\PaymentSdk\Transaction\WeChatTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\SubMerchantInfo;
use WirecardEE\Prestashop\Helper\AdditionalInformation;

/**
 * Class PaymentWeChat
 *
 * @extends Payment
 *
 * @since 1.4.0
 */
class PaymentWeChat extends Payment
{

    /**
     * PaymentWeChat constructor.
     *
     * @since 1.4.0
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->type = 'wechat-qrpay';
        $this->name = 'Wirecard WeChat QRPay';
        $this->formFields = $this->createFormFields();

        $this->refund  = array('debit');
    }

    /**
     * Create form fields for WeChat
     *
     * @return array|null
     * @since 1.4.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'WeChat',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_wechat'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_wechat'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label' => $this->l('config_merchant_account_id'),
                    'type' => 'text',
                    'default' => '20216dc1-0656-454a-94a1-ee51140d57fa',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label' => $this->l('config_merchant_secret'),
                    'type' => 'text',
                    'default' => '9486b283-778f-4623-a70a-9ca663928d28',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label' => $this->l('config_base_url'),
                    'type' => 'text',
                    'doc' => $this->l('config_base_url_desc'),
                    'default' => 'https://api-wdcee-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label' => $this->l('config_http_user'),
                    'type' => 'text',
                    'default' => 'wechat_sandbox',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label' => $this->l('config_http_password'),
                    'type' => 'text',
                    'default' => '9p0q8w8i',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type' => 'hidden',
                    'default' => 'pay',
                ),
                array(
                    'name' => 'sub_merchant_id',
                    'label' => $this->l('config_sub_merchant_id'),
                    'type' => 'text',
                    'doc' => $this->l('config_sub_merchant_id_desc'),
                    'default' => '12152566',
                    'required' => true,
                ),
                array(
                    'name' => 'sub_merchant_name',
                    'label' => $this->l('config_sub_merchant_name'),
                    'type' => 'text',
                    'doc' => $this->l('config_sub_merchant_name_desc'),
                    'default' => 'store name',
                    'required' => false,
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
                    'id' => 'wechatConfig',
                    'method' => 'wechat-qrpay',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_WECHAT_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_WECHAT_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_WECHAT_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for WeChat transactions
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
        $paymentConfig = new PaymentMethodConfig(WeChatTransaction::NAME, $merchantAccountId, $secret);
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create WeChat transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|WeChatTransaction
     * @since 1.4.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new WeChatTransaction();

        $additionalInformation = new AdditionalInformation();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));
        $transaction->setOrderDetail($additionalInformation->createDescriptor($orderId));

        $subMerchantInfo = new SubMerchantInfo();
        $subMerchantInfo->setMerchantId($module->getConfigValue($this->type, 'sub_merchant_id'));

        if ($module->getConfigValue($this->type, 'sub_merchant_name')) {
            $subMerchantInfo->setMerchantName($module->getConfigValue($this->type, 'sub_merchant_name'));
        }

        $transaction->setSubMerchantInfo($subMerchantInfo);

        return $transaction;
    }

    /**
     * Create refund transaction
     *
     * @param $transactionData
     * @return WeChatTransaction
     * @since 1.4.0
     */
    public function createRefundTransaction($transactionData)
    {
        return $this->createCancelTransaction($transactionData);
    }

    /**
     * Create cancel transaction
     *
     * @param $transactionData
     * @return WeChatTransaction
     * @since 1.4.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new WeChatTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setAmount(new Amount($transactionData->amount, $transactionData->currency));

        return $transaction;
    }
}
