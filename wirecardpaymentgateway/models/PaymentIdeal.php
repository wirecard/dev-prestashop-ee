<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\IdealBic;
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
        $this->name = 'Wirecard iDEAL';
        $this->formFields = $this->createFormFields();

        $this->refund  = array('debit');
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
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_ideal'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_ideal'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->l('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '4aeccf39-0d47-47f6-a399-c05c1f2fc819',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->l('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => '7a353766-23b5-4992-ae96-cb4232998954',
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
                    'name' => 'payment_action',
                    'type'    => 'hidden',
                    'default' => 'pay',
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
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->l('test_config'),
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
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new IdealTransaction();
        if (isset($values['idealBankBic'])) {
            $transaction->setBic($values['idealBankBic']);
        }

        return $transaction;
    }

    /**
     * Create refund iDEALTransaction
     *
     * @param $transactionData
     * @param $module
     * @return SepaCreditTransferTransaction
     * @since 1.0.0
     */
    public function createRefundTransaction($transactionData, $module)
    {
        $sepa = new PaymentSepaCreditTransfer();
        return $sepa->createRefundTransaction($transactionData, $module);
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
