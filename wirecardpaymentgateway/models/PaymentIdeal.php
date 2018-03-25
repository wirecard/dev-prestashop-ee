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
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\IdealBic;

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
     * PaymentiDEAL constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type = 'ideal';
        $this->name = 'Wirecard Payment Processing Gateway iDEAL';
        $this->formFields = $this->createFormFields();
        $this->setAdditionalInformationTemplate($this->type, $this->setTemplateData());
        $this->setLoadJs(true);
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
                    'label' => 'Enable',
                    'type' => 'onoff',
                    'doc' => 'Enable Wirecard Payment Processing Gateway iDEAL',
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Wirecard Payment Processing Gateway iDEAL',
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => 'Merchant Account ID',
                    'type'    => 'text',
                    'default' => 'b4ca14c0-bb9a-434d-8ce3-65fbff2c2267',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => 'Secret key',
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => 'Base url',
                    'type'        => 'text',
                    'doc' => 'The elastic engine base url. (e.g. https://api.wirecard.com)',
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => 'Http user',
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => 'Http password',
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'pay',
                    'label'   => 'Payment action',
                    'options' => array(
                        array('key' => 'pay', 'value' => 'Capture'),
                    ),
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => 'Enable descriptor',
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => 'Send additional information',
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => 'Test iDEAL configuration',
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
     * Create config for iDEAL transactions
     *
     * @param \WirecardPaymentGateway $paymentModule
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 1.0.0
     */
    public function createPaymentConfig($paymentModule)
    {
        $baseUrl  = $paymentModule->getConfigValue($this->type, 'base_url');
        $httpUser = $paymentModule->getConfigValue($this->type, 'http_user');
        $httpPass = $paymentModule->getConfigValue($this->type, 'http_pass');

        $merchantAccountId = $paymentModule->getConfigValue($this->type, 'merchant_account_id');
        $secret = $paymentModule->getConfigValue($this->type, 'secret');

        $config = $this->createConfig($baseUrl, $httpUser, $httpPass);
        $paymentConfig = new PaymentMethodConfig(IdealTransaction::NAME, $merchantAccountId, $secret);
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create iDEALTransaction
     *
     * @return iDEALTransaction
     * @since 1.0.0
     */
    public function createTransaction()
    {
        $transaction = new IdealTransaction();

        return $transaction;
    }

    /**
     * Returns all supported banks from iDEAL
     *
     * @return array
     * @since 1.0.0
     */
    private function setTemplateData()
    {
        return array('banks' => array(
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
        ));
    }
}
