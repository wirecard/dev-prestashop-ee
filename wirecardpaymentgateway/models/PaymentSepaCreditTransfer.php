<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use WirecardEE\Prestashop\Helper\AdditionalInformationBuilder;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

/**
 * Class PaymentSepaDirectDebit
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentSepaCreditTransfer extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = SepaCreditTransferTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentsepacredittransfer";

    /**
     * PaymentSepaDirectDebit constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard SEPA Credit Transfer';
        $this->formFields = $this->createFormFields();
        $this->setLoadJs(true);
    }

    /**
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @return bool
     */
    public function isAvailable($module, $cart)
    {
        return false;
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
            'tab' => 'sepacredittransfer',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_sepact'),
                    'default' => 0,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '59a01668-693b-49f0-8a1f-f3c1ba025d45',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'ecdf5990-0372-47cd-a55d-037dccfe9d25',
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
                    'default' => '16390-testing',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->getTranslatedString('config_http_password'),
                    'type'    => 'text',
                    'default' => '3!3013=D3fD8X7',
                    'required' => true,
                ),

                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->getTranslatedString('test_config'),
                    'id' => 'SepaCreditTransferConfig',
                    'method' => 'sepacredittransfer',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_SEPACREDITTRANSFER_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_SEPACREDITTRANSFER_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_SEPACREDITTRANSFER_HTTP_PASS'
                    )
                )
            )
        );
    }

    public function createTransaction($operation = null)
    {
        return $this->getTransactionInstance($operation);
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @param $operation
     * @return SepaCreditTransferTransaction
     * @since 2.4.0
     */
    public function getTransactionInstance($operation = null)
    {
        return new SepaCreditTransferTransaction();
    }
}
