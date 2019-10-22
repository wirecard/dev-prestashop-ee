<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;

/**
 * Class PaymentSofort
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentSofort extends Payment
{
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = SofortTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentsofort";

    /**
     * PaymentSofort constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->type = self::TYPE;
        $this->name = 'Wirecard Sofort.';
        $this->formFields = $this->createFormFields();

        $this->setLogo(
            'https://cdn.klarna.com/1.0/shared/image/generic/badge/de_de/pay_now/standard/pink.svg'
        );
    }

    /**
     * Create form fields for Sofort.
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'Sofort',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_sofortbanking'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_sofortbanking'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '6c0e7efd-ee58-40f7-9bbd-5e7337a052cd',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => '58764ab3-5c56-450e-b747-7237a24e92a7',
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
                    'name' => 'payment_action',
                    'type'    => 'hidden',
                    'default' => 'pay',
                ),
                array(
                    'name' => 'descriptor',
                    'type'    => 'hidden',
                    'default' => 1,
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
                    'id' => 'sofortbankingConfig',
                    'method' => 'sofortbanking',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_SOFORT_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_SOFORT_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_SOFORT_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create Sofort transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|SofortTransaction
     * @since 1.0.0
     */
    public function createTransaction($operation = null)
    {
        return $this->createTransactionInstance($operation);
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @param string $operation
     * @return SofortTransaction|SepaCreditTransferTransaction
     * @since 2.4.0
     */
    public function createTransactionInstance($operation = null)
    {
        if (Operation::CREDIT === $operation) {
            return new SepaCreditTransferTransaction();
        }

        return new SofortTransaction();
    }
}
