<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use WirecardEE\Prestashop\Helper\AdditionalInformationBuilder;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Class PaymentPtwentyfour
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentPtwentyfour extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = PtwentyfourTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentptwentyfour";

    /**
     * PaymentPtwentyfour constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard Przelewy24';
        $this->formFields = $this->createFormFields();

        $this->refund  = array('debit');
    }

    /**
     * Create form fields for Pwentyfour
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'P24',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_ptwentyfour'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_ptwentyfour'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => 'afb0aa46-3b0b-4cbf-a91c-5c91ede23701',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => '82fd2e9e-f8e9-42fb-be25-b60a6907c996',
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
                    'id' => 'p24Config',
                    'method' => 'P24',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_P24_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_P24_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_P24_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create Ptwentyfour transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|PtwentyfourTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new PtwentyfourTransaction();

        $additionalInformation = new AdditionalInformationBuilder();
        $transaction->setAccountHolder($additionalInformation->createAccountHolder($cart, 'billing'));

        return $transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param $transactionData
     * @return PtwentyfourTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $transaction = new PtwentyfourTransaction();
        $transaction->setParentTransactionId($transactionData->transaction_id);

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @return PtwentyfourTransaction
     * @since 2.3.0
     */
    public function getTransactionInstance()
    {
        return new PtwentyfourTransaction();
    }
}
