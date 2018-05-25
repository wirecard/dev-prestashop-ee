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

use Wirecard\PaymentSdk\Transaction\UpiTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use WirecardEE\Prestashop\Helper\AdditionalInformation;

/**
 * Class PaymentUnionPayInternational
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentUnionPayInternational extends Payment
{
    /**
     * PaymentUnionPayInternational constructor.
     *
     * @since 1.0.0
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->type = 'unionpayinternational';
        $this->name = 'Wirecard UnionPay International';
        $this->formFields = $this->createFormFields();
        $this->setAdditionalInformationTemplate($this->type);
        $this->setLoadJs(true);

        $this->cancel  = array('authorization');
        $this->capture = array('authorization');
        $this->refund  = array('purchase', 'capture-authorization');
    }

    /**
     * Create form fields for UnionPayInternational
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'unionpayinternational',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => 'Enable',
                    'type' => 'onoff',
                    'doc' => $this->translate('upi_enable_doc'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => $this->translate('upi_title_doc'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->translate('merchant_id_doc'),
                    'type'    => 'text',
                    'default' => 'c6e9331c-5c1f-4fc6-8a08-ef65ce09ddb0',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->translate('secret_key_doc'),
                    'type'    => 'text',
                    'default' => '16d85b73-79e2-4c33-932a-7da99fb04a9c',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => $this->translate('base_url_doc'),
                    'type'        => 'text',
                    'doc' => $this->translate('base_url_example_doc'),
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->translate('http_user_doc'),
                    'type'    => 'text',
                    'default' => '70000-APILUHN-CARD',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->translate('http_pass_doc'),
                    'type'    => 'text',
                    'default' => '8mhwavKVb91T',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'pay',
                    'label'   => $this->translate('payment_action_doc'),
                    'options' => array(
                        array('key' => 'reserve', 'value' => $this->translate('payment_action_auth_doc')),
                        array('key' => 'pay', 'value' => $this->translate('payment_action_capture_doc')),
                    ),
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => $this->translate('descriptor_doc'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => $this->translate('send_addit_info_doc'),
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->translate('upi_test_config_butoon_doc'),
                    'id' => 'unionpayinternationalConfig',
                    'method' => 'unionpayinternational',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for UnionPayInternational transactions
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
        $paymentConfig = new PaymentMethodConfig(
            UpiTransaction::NAME,
            $merchantAccountId,
            $secret
        );
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create request data for UnionPayInternational UI
     *
     * @param \WirecardPaymentGateway $module
     * @return mixed
     * @since 1.0.0
     */
    public function getRequestData($module)
    {
        $config = $this->createPaymentConfig($module);
        $transactionService = new TransactionService($config);
        return $transactionService->getDataForUpiUi();
    }

    /**
     * Create UnionPayInternational transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|UpiTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new UpiTransaction();

        $transaction->setTokenId($values['tokenId']);
        $transaction->setTermUrl($module->createRedirectUrl($orderId, $this->type, 'success'));

        return $transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param $transactionData
     * @return UpiTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new UpiTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setAmount(new Amount($transactionData->amount, $transactionData->currency));

        return $transaction;
    }

    /**
     * Create pay transaction
     *
     * @param Transaction $transactionData
     * @return UpiTransaction
     * @since 1.0.0
     */
    public function createPayTransaction($transactionData)
    {
        $transaction = new UpiTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);
        $transaction->setAmount(new Amount($transactionData->amount, $transactionData->currency));

        return $transaction;
    }
}
