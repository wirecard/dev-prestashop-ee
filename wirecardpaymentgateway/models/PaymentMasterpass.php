<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use WirecardEE\Prestashop\Helper\AdditionalInformationBuilder;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Class PaymentMasterpass
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentMasterpass extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = MasterpassTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentmasterpass";

    /**
     * PaymentiDEAL constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard Masterpass';
        $this->formFields = $this->createFormFields();
    }

    /**
     * Create form fields for iDEAL
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'masterpass',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_masterpass'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_masterpass'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '8bc8ed6d-81a8-43be-bd7b-75b008f89fa6',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => '2d96596b-9d10-4c98-ac47-4d56e22fd878',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => $this->getTranslatedString('config_base_url'),
                    'type'        => 'text',
                    'doc' => $this->getTranslatedString('config_base_url_desc'),
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->getTranslatedString('config_http_user'),
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->getTranslatedString('config_http_password'),
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'authorization',
                    'label'   => $this->getTranslatedString('config_payment_action'),
                    'options' => array(
                        array('key' => 'reserve', 'value' => $this->getTranslatedString('text_payment_action_reserve')),
                        array('key' => 'pay', 'value' => $this->getTranslatedString('text_payment_action_pay')),
                    ),
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => $this->getTranslatedString('config_descriptor'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => $this->getTranslatedString('config_additional_info'),
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->getTranslatedString('test_config'),
                    'id' => 'masterpassConfig',
                    'method' => 'Masterpass',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_MASTERPASS_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_MASTERPASS_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_MASTERPASS_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create Masterpass transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|MasterpassTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new MasterpassTransaction();

        $additionalInformation = new AdditionalInformationBuilder();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));

        return $transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param $transactionData
     * @return MasterpassTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new MasterpassTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        return $transaction;
    }

    /**
     * Create pay transaction
     *
     * @param Transaction $transactionData
     * @return MasterpassTransaction
     * @since 1.0.0
     */
    public function createPayTransaction($transactionData)
    {
        $transaction = new MasterpassTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @return MasterpassTransaction
     * @since 2.3.0
     */
    public function getTransactionInstance()
    {
        return new MasterpassTransaction();
    }
}
