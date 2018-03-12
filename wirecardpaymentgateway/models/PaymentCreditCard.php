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

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Class PaymentCreditCard
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentCreditCard extends Payment
{
    /**
     * PaymentCreditCard constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type = 'creditcard';
        $this->name = 'Wirecard Payment Processing Gateway Credit Card';
        $this->formFields = $this->createFormFields();
        $this->setAdditionalInformationTemplate($this->type);
    }

    /**
     * Create form fields for creditcard
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'CreditCard',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => 'Enable/Disable',
                    'type' => 'onoff',
                    'doc' => 'Enable Wirecard Payment Processing Gateway Credit Card',
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Wirecard Payment Processing Gateway Credit Card',
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => 'Merchant Account ID',
                    'type'    => 'text',
                    'default' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => 'Secret Key',
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_merchant_account_id',
                    'label'    => '3-D Secure Merchant Account ID',
                    'type'     => 'text',
                    'default'  => '508b8896-b37d-4614-845c-26bf8bf2c948',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_secret',
                    'label'       => '3-D Secure Secret Key',
                    'type'        => 'text',
                    'default'     => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'ssl_max_limit',
                    'label'       => 'Non 3-D Secure Max Limit',
                    'type'        => 'text',
                    'default'     => '100.0',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_min_limit',
                    'label'       => '3-D Secure Min Limit',
                    'type'        => 'text',
                    'default'     => '50.0',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => 'Base Url',
                    'type'        => 'text',
                    'doc' => 'The elastic engine base url. (e.g. https://api.wirecard.com)',
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => 'Http User',
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => 'Http Password',
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'reserve',
                    'label'   => 'Payment Action',
                    'options' => array(
                        array('key' => 'reserve', 'value' => 'Authorization'),
                        array('key' => 'pay', 'value' => 'Capture'),
                    ),
                ),
                array(
                    'name' => 'shopping_basket',
                    'label'   => 'Enable/Disable Shopping Basket',
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => 'Enable/Disable Descriptor',
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => 'Enable/Disable send additional information',
                    'type'    => 'onoff',
                    'default' => 1,
                ),
            )
        );
    }

    /**
     * Create config for credit card transactions
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
        $paymentConfig = new CreditCardConfig($merchantAccountId, $secret);
        $config->add($paymentConfig);

        if ($paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id') !== '') {
            $paymentConfig->setThreeDCredentials(
                $paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id'),
                $paymentModule->getConfigValue($this->type, 'three_d_secret')
            );
        }

        if ($paymentModule->getConfigValue($this->type, 'ssl_max_limit') !== '') {
            $paymentConfig->addSslMaxLimit(
                new Amount(
                    $paymentModule->getConfigValue($this->type, 'ssl_max_limit'),
                    'EUR'
                )
            );
        }

        if ($paymentModule->getConfigValue($this->type, 'three_d_min_limit') !== '') {
            $paymentConfig->addThreeDMinLimit(
                new Amount(
                    $paymentModule->getConfigValue($this->type, 'three_d_min_limit'),
                    'EUR'
                )
            );
        }

        $config->add($paymentConfig);

        return $config;
    }

    public function getRequestData($module)
    {
        $config = $this->createPaymentConfig($module);
        $transactionService = new TransactionService($config);
        return $transactionService->getDataForCreditCardUi();
    }

    /**
     * Create CreditCardTransaction
     *
     * @param array
     * @return CreditCardTransaction
     * @since 1.0.0
     */
    public function createTransaction($formFields)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setTokenId($formFields['tokenId']);

        return $transaction;
    }
}
