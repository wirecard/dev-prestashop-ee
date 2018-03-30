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

use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Config\SepaConfig;
use WirecardEE\Prestashop\Helper\AdditionalInformation;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;

/**
 * Class PaymentSepa
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentSepa extends Payment
{
    /**
     * PaymentSepa constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type = 'sepa';
        $this->name = 'Wirecard SEPA';
        $this->formFields = $this->createFormFields();
        $this->setAdditionalInformationTemplate($this->type, $this->setTemplateData());
        $this->setLoadJs(true);

        $this->cancel  = array('pending-debit');
        $this->capture = array('authorization');
        $this->refund  = array('debit');
    }

    /**
     * Create form fields for SEPA
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'SEPA',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => 'Enable',
                    'type' => 'onoff',
                    'doc' => 'Enable Wirecard SEPA',
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Wirecard SEPA',
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => 'Merchant Account ID',
                    'type'    => 'text',
                    'default' => '4c901196-eff7-411e-82a3-5ef6b6860d64',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => 'Secret Key',
                    'type'    => 'text',
                    'default' => 'ecdf5990-0372-47cd-a55d-037dccfe9d25',
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
                    'name' => 'creditor_id',
                    'label'   => 'Creditor ID',
                    'type'    => 'text',
                    'default' => 'DE98ZZZ09999999999',
                    'required' => true,
                ),
                array(
                    'name' => 'creditor_name',
                    'label'   => 'Creditor Name',
                    'type'    => 'text',
                    'default' => '',
                    'required' => false,
                ),
                array(
                    'name' => 'creditor_city',
                    'label'   => 'Creditor City',
                    'type'    => 'text',
                    'default' => '',
                    'required' => false,
                ),
                array(
                    'name' => 'sepa_mandate_textextra',
                    'label'   => 'Additional text',
                    'type'    => 'textarea',
                    'doc'     => 'Text entered here will be shown on the SEPA mandate page at the end of the first 
                    paragraph.',
                    'empty_message' => 'Click here and type your text',
                    'default' => '',
                    'required' => false,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'authorization',
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
                    'name' => 'enable_bic',
                    'label'   => 'BIC enabled',
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => 'Test configuration',
                    'id' => 'SepaConfig',
                    'method' => 'SEPA',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_SEPA_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_SEPA_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_SEPA_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for SEPA transactions
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
        $paymentConfig = new SepaConfig($merchantAccountId, $secret);
        $paymentConfig->setCreditorId($paymentModule->getConfigValue($this->type, 'creditor_id'));
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create sepa transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|SepaTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new SepaTransaction();
        if (isset($values['sepaFirstName']) && isset($values['sepaLastName']) && isset($values['sepaIban'])) {
            $account_holder = new AccountHolder();
            $account_holder->setFirstName($values['sepaFirstName']);
            $account_holder->setLastName($values['sepaLastName']);

            $transaction->setAccountHolder($account_holder);
            $transaction->setIban($values['sepaIban']);

            if ($module->getConfigValue('sepa', 'enable_bic')) {
                if (isset($values['sepaBic'])) {
                    $transaction->setBic($values['sepaBic']);
                }
            }

            $mandate = new Mandate($this->generateMandateId($module, $orderId));
            $transaction->setMandate($mandate);
        }

        return $transaction;
    }

    /**
     * Create refund SepaTransaction
     *
     * @param Transaction $transactionData
     * @return SepaTransaction
     * @since 1.0.0
     */
    public function createRefundTransaction($transactionData)
    {
        $transaction = new SepaTransaction();

        $additionalInformation = new AdditionalInformation();
        $cart = new \Cart($transactionData->cart_id);
        $transaction->setAccountHolder($additionalInformation->createAccountHolder(
            $cart,
            'billing'
        ));
        $transaction->setParentTransactionId($transactionData->transaction_id);

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
                \Tools::strtoupper('enable_bic')
            )
        );

        return array('bicEnabled' => (bool) $test);
    }

    /**
     * Generate the mandate id for SEPA
     *
     * @param int $orderId
     * @return string
     * @since 1.0.0
     */
    public function generateMandateId($paymentModule, $orderId)
    {
        return $paymentModule->getConfigValue($this->type, 'creditor_id') . '-' . $orderId
            . '-' . strtotime(date('Y-m-d H:i:s'));
    }
}
