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
                    'default' => $this->credentialsConfig->getMerchantAccountId(),
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => $this->credentialsConfig->getSecret(),
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => $this->getTranslatedString('config_base_url'),
                    'type'        => 'text',
                    'doc' => $this->getTranslatedString('config_base_url_desc'),
                    'default'     => $this->credentialsConfig->getBaseUrl(),
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->getTranslatedString('config_http_user'),
                    'type'    => 'text',
                    'default' => $this->credentialsConfig->getHttpUser(),
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->getTranslatedString('config_http_password'),
                    'type'    => 'text',
                    'default' => $this->credentialsConfig->getHttpPassword(),
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
    public function createTransaction($operation = null)
    {
        $context = \Context::getContext();
        $cart = $context->cart;
        $additionalInformation = new AdditionalInformationBuilder();

        $transaction = $this->createTransactionInstance($operation);
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));
        $transaction->setShipping($additionalInformation->createAccountHolder($cart, 'shipping'));

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @param string $operation
     * @return PayPalTransaction
     * @since 2.4.0
     */
    public function createTransactionInstance($operation = null)
    {
        return new PayPalTransaction();
    }
}
