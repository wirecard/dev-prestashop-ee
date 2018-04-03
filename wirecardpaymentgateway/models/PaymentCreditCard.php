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
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv3
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
        $this->name = 'Wirecard Credit Card';
        $this->formFields = $this->createFormFields();
        $this->setAdditionalInformationTemplate($this->type, $this->setTemplateData());
        $this->setLoadJs(true);

        $this->cancel  = array('authorization');
        $this->capture = array('authorization');
        $this->refund  = array('purchase', 'capture-authorization');
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
                    'label' => 'Enable',
                    'type' => 'onoff',
                    'doc' => 'Enable Wirecard Credit Card',
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Wirecard Credit Card',
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
                    'default'     => '300.0',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_min_limit',
                    'label'       => '3-D Secure Min Limit',
                    'type'        => 'text',
                    'default'     => '100.0',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => 'Base URL',
                    'type'        => 'text',
                    'doc' => 'The elastic engine base url. (e.g. https://api.wirecard.com)',
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => 'HTTP User',
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => 'HTTP Password',
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'pay',
                    'label'   => 'Payment Action',
                    'options' => array(
                        array('key' => 'reserve', 'value' => 'Authorization'),
                        array('key' => 'pay', 'value' => 'Capture'),
                    ),
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => 'Descriptor',
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => 'Send Additional Information',
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'ccvault_enabled',
                    'label'=> 'Enable One-Click checkout',
                    'type' => 'onoff',
                    'default' => 0
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => 'Test Credit Card configuration',
                    'id' => 'creditcardConfig',
                    'method' => 'creditcard',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_HTTP_PASS'
                    )
                )
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

        if ($paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id') !== '') {
            $paymentConfig->setThreeDCredentials(
                $paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id'),
                $paymentModule->getConfigValue($this->type, 'three_d_secret')
            );
        }

        if (is_numeric($paymentModule->getConfigValue($this->type, 'ssl_max_limit')) &&
            $paymentModule->getConfigValue($this->type, 'ssl_max_limit') >= 0) {
            $paymentConfig->addSslMaxLimit(
                new Amount(
                    $paymentModule->getConfigValue($this->type, 'ssl_max_limit'),
                    'EUR'
                )
            );
        }

        if (is_numeric($paymentModule->getConfigValue($this->type, 'three_d_min_limit')) &&
            $paymentModule->getConfigValue($this->type, 'three_d_min_limit') >= 0) {
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

    /**
     * Create request data for credit card ui
     *
     * @param \WirecardPaymentGateway $module
     * @return mixed
     * @since 1.0.0
     */
    public function getRequestData($module)
    {
        $config = $this->createPaymentConfig($module);
        $transactionService = new TransactionService($config);
        return $transactionService->getDataForCreditCardUi();
    }

    /**
     * Create creditcard transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|CreditCardTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new CreditCardTransaction();

        return $transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param $transactionData
     * @return CreditCardTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setParentTransactionType($transactionData->transaction_type);
        $transaction->setAmount(new Amount($transactionData->amount, $transactionData->currency));

        return $transaction;
    }

    /**
     * Create pay transaction
     *
     * @param Transaction $transactionData
     * @return CreditCardTransaction
     * @since 1.0.0
     */
    public function createPayTransaction($transactionData)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setAmount(new Amount($transactionData->amount, $transactionData->currency));

        return $transaction;
    }

    /**
     * Set template variables
     *
     * @return array
     * @since 1.0.0
     */
    private function setTemplateData()
    {
        $test = \Configuration::get(
            sprintf(
                'WIRECARD_PAYMENT_GATEWAY_%s_%s',
                \Tools::strtoupper($this->type),
                \Tools::strtoupper('ccvault_enabled')
            )
        );

        return array('ccvaultenabled' => (bool) $test);
    }
}
