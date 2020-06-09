<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Entity\IdealBic;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;

/**
 * Class PaymentiDEAL
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentIdeal extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = IdealTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentideal";

    /**
     * PaymentiDEAL constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'iDEAL';
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
            'tab' => 'ideal',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_ideal'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_ideal'),
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
                    'id' => 'idealConfig',
                    'method' => 'iDEAL',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_IDEAL_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_IDEAL_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_IDEAL_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create ideal transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|IdealTransaction
     * @since 1.0.0
     */
    public function createTransaction($operation = null)
    {
        $values = \Tools::getAllValues();
        $transaction = $this->createTransactionInstance($operation);

        if (isset($values['idealBankBic'])) {
            $transaction->setBic($values['idealBankBic']);
        }

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @param string $operation
     * @return IdealTransaction|SepaCreditTransferTransaction
     * @since 2.4.0
     */
    public function createTransactionInstance($operation = null)
    {
        if (Operation::CREDIT === $operation) {
            return new SepaCreditTransferTransaction();
        }

        return new IdealTransaction();
    }

    /**
     * Returns all supported banks from iDEAL
     *
     * @return array
     * @since 1.0.0
     */
    protected function getFormTemplateData()
    {
        return array(
            'banks' => array(
                array('key' => IdealBic::ABNANL2A, 'label' => 'ABN Amro Bank'),
                array('key' => IdealBic::ASNBNL21, 'label' => 'ASN Bank'),
                array('key' => IdealBic::BUNQNL2A, 'label' => 'bunq'),
                array('key' => IdealBic::INGBNL2A, 'label' => 'ING'),
                array('key' => IdealBic::KNABNL2H, 'label' => 'Knab'),
                array('key' => IdealBic::RABONL2U, 'label' => 'Rabobank'),
                array('key' => IdealBic::RGGINL21, 'label' => 'Regio Bank'),
                array('key' => IdealBic::SNSBNL2A, 'label' => 'SNS Bank'),
                array('key' => IdealBic::TRIONL2U, 'label' => 'Triodos Bank'),
                array('key' => IdealBic::FVLBNL22, 'label' => 'Van Lanschot Bankiers')
            )
        );
    }
}
