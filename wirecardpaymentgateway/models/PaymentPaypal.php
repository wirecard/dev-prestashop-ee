<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use WirecardEE\Prestashop\Helper\AdditionalInformationBuilder;

/**
 * Class PaymentPaypal
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentPaypal extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = PayPalTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentpaypal";

    /**
     * PaymentPaypal constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard PayPal';
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
            'tab' => 'PayPal',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_paypal'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_paypal'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '2a0e9351-24ed-4110-9a1b-fd0fee6bec26',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
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
                    'name' => 'shopping_basket',
                    'label'   => $this->getTranslatedString('config_shopping_basket'),
                    'type'    => 'onoff',
                    'default' => 0,
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
     * Create paypal transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|PayPalTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $additionalInformation = new AdditionalInformationBuilder();

        $transaction = new PayPalTransaction();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));
        $transaction->setShipping($additionalInformation->createAccountHolder($cart, 'shipping'));

        return $transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param Transaction $transactionData
     * @return PayPalTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new PayPalTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        return $transaction;
    }

    /**
     * Create pay transaction
     *
     * @param Transaction $transactionData
     * @return PayPalTransaction
     * @since 1.0.0
     */
    public function createPayTransaction($transactionData)
    {
        $transaction = new PayPalTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        return $transaction;
    }
}
