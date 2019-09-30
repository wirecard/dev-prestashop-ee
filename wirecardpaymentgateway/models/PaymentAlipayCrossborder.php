<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use WirecardEE\Prestashop\Helper\AdditionalInformationBuilder;

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
     * @var string
     * @since 2.1.0
     */
    const TYPE = AlipayCrossborderTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentalipaycrossborder";

    /**
     * PaymentAlipayCrossborder constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard Alipay Crossborder';
        $this->formFields = $this->createFormFields();
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
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_alipay_crossborder'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_alipay_crossborder'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '7ca48aa0-ab12-4560-ab4a-af1c477cce43',
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
                    'type'    => 'hidden',
                    'default' => 'pay',
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

        $additionalInformation = new AdditionalInformationBuilder();
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

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @return AlipayCrossborderTransaction
     * @since 2.3.0
     */
    public function getTransactionInstance()
    {
        return new AlipayCrossborderTransaction();
    }
}
