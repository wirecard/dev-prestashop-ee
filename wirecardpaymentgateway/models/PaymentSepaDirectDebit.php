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

use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;

/**
 * Class PaymentSepaDirectDebit
 *
 * @extends Payment
 *
 * @since 1.3.0
 */
class PaymentSepaDirectDebit extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = SepaDirectDebitTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentsepadirectdebit";

    /**
     * PaymentSepaDirectDebit constructor.
     *
     * @since 1.3.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard SEPA Direct Debit';
        $this->formFields = $this->createFormFields();
        $this->setLoadJs(true);

        $this->cancel  = array('pending-debit');
        $this->capture = array('authorization');
        $this->refund  = array('debit');
    }

    /**
     * Create form fields for SEPA
     *
     * @return array|null
     * @since 1.3.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'sepadirectdebit',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_sepadd'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_sepadd'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->l('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '933ad170-88f0-4c3d-a862-cff315ecfbc0',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->l('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => '5caf2ed9-5f79-4e65-98cb-0b70d6f569aa',
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
                    'default' => '16390-testing',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->l('config_http_password'),
                    'type'    => 'text',
                    'default' => '3!3013=D3fD8X7',
                    'required' => true,
                ),
                array(
                    'name' => 'creditor_id',
                    'label'   => $this->l('config_creditor_id'),
                    'type'    => 'text',
                    'default' => 'DE98ZZZ09999999999',
                    'required' => true,
                ),
                array(
                    'name' => 'creditor_name',
                    'label'   => $this->l('config_creditor_name'),
                    'type'    => 'text',
                    'default' => '',
                    'required' => false,
                ),
                array(
                    'name' => 'creditor_city',
                    'label'   => $this->l('config_creditor_city'),
                    'type'    => 'text',
                    'default' => '',
                    'required' => false,
                ),
                array(
                    'name' => 'sepadirectdebit_textextra',
                    'label'   => $this->l('config_mandate_text'),
                    'type'    => 'textarea',
                    'doc'     => $this->l('config_mandate_text_desc'),
                    'default' => '',
                    'required' => false,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'authorization',
                    'label'   => $this->l('config_payment_action'),
                    'options' => array(
                        array('key' => 'reserve', 'value' => $this->l('text_payment_action_reserve')),
                        array('key' => 'pay', 'value' => $this->l('text_payment_action_pay')),
                    ),
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
                    'name' => 'enable_bic',
                    'label'   => $this->l('config_enable_bic'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->l('test_config'),
                    'id' => 'SepaDirectDebitConfig',
                    'method' => 'sepadirectdebit',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_SEPADIRECTDEBIT_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_SEPADIRECTDEBIT_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_SEPADIRECTDEBIT_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create sepa transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|SepaDirectDebitTransaction
     * @since 1.3.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new SepaDirectDebitTransaction();

        if (isset($values['sepaFirstName']) && isset($values['sepaLastName']) && isset($values['sepaIban'])) {
            $account_holder = new AccountHolder();
            $account_holder->setFirstName($values['sepaFirstName']);
            $account_holder->setLastName($values['sepaLastName']);

            $transaction->setAccountHolder($account_holder);
            $transaction->setIban($values['sepaIban']);

            if ($this->configuration->getField('enable_bic')) {
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
     * Create refund SepaDirectDebitTransaction
     *
     * @param Transaction $transactionData
     * @return SepaDirectDebitTransaction
     * @since 1.3.0
     */
    public function createRefundTransaction($transactionData, $module)
    {
        $sepa = new PaymentSepaCreditTransfer($module);
        return $sepa->createRefundTransaction($transactionData, $module);
    }

    /**
     * Set template variables
     *
     * @return array
     * @since 1.3.0
     */
    public function getFormTemplateData()
    {
        return array(
            'creditorName'      => $this->configuration->getField('creditor_name'),
            'creditorStoreCity' => $this->configuration->getField('creditor_city'),
            'creditorId'        => $this->configuration->getField('creditor_id'),
            'additionalText'    => $this->configuration->getField('sepadirectdebit_textextra'),
            'bicEnabled'        => (bool) $this->configuration->getField('enable_bic'),
            'date'              => date('d.m.Y'),
        );
    }

    /**
     * Generate the mandate id for SEPA
     *
     * @param int $orderId
     * @return string
     * @since 1.3.0
     */
    public function generateMandateId($paymentModule, $orderId)
    {
        return $this->configuration->getField('creditor_id') . '-' . $orderId
            . '-' . strtotime(date('Y-m-d H:i:s'));
    }
}
