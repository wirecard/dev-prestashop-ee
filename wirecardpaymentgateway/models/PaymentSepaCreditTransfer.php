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

        $this->cancel  = array('pending-debit');
        $this->capture = array('authorization');
        $this->refund  = array('debit');
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
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_sepact'),
                    'default' => 0,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->l('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '59a01668-693b-49f0-8a1f-f3c1ba025d45',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->l('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'ecdf5990-0372-47cd-a55d-037dccfe9d25',
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
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->l('test_config'),
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

    /**
     * Create sepa transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|SepaCreditTransferTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $transaction = new SepaCreditTransferTransaction();
        return $transaction;
    }

    /**
     * Create refund SepaCreditTransferTransaction
     *
     * @param Transaction $transactionData
     * @return SepaCreditTransferTransaction
     * @since 1.0.0
     */
    public function createRefundTransaction($transactionData, $module)
    {
        $transaction = new SepaCreditTransferTransaction();

        $additionalInformation = new AdditionalInformationBuilder();
        $cart = new \Cart($transactionData->cart_id);
        $transaction->setAccountHolder($additionalInformation->createAccountHolder(
            $cart,
            'billing'
        ));
        $transaction->setParentTransactionId($transactionData->transaction_id);

        return $transaction;
    }

    /**
     * Generate the mandate id for SEPA
     *
     * @param int $orderId
     * @return string
     * @since 1.0.0
     */
    public function generateMandateId($paymentModule, $orderId)
    {
        $paymentConfiguration = new ShopConfigurationService(static::TYPE);

        return $paymentConfiguration->getField('creditor_id') . '-' . $orderId
            . '-' . strtotime(date('Y-m-d H:i:s'));
    }
}
