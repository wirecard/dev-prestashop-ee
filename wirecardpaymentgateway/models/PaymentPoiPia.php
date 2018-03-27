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

use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Class PaymentPoiPia
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentPoiPia extends Payment
{
    /**
     * PaymentPoiPia constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type = 'poipia';
        $this->name = 'Wirecard Payment Processing Gateway Payment on Invoice / Payment in Advance';
        $this->formFields = $this->createFormFields();

        $this->cancel  = array('authorization');
    }

    /**
     * Create form fields for POI/PIA
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'POIPIA',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => 'Enable',
                    'type' => 'onoff',
                    'doc' => 'Enable Wirecard Payment Processing Gateway Payment on Invoice / Payment in Advance',
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Wirecard Payment Processing Gateway Payment on Invoice / Payment in Advance',
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => 'Merchant Account ID',
                    'type'    => 'text',
                    'default' => '105ab3e8-d16b-4fa0-9f1f-18dd9b390c94',
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
                    'name' => 'payment_type',
                    'type'    => 'select',
                    'default' => 'pia',
                    'label'   => 'Payment type',
                    'options' => array(
                        array('key' => 'pia', 'value' => 'Payment in Advance'),
                        array('key' => 'poi', 'value' => 'Payment on Invoice'),
                    ),
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'reserve',
                    'label'   => 'Payment action',
                    'options' => array(
                        array('key' => 'reserve', 'value' => 'Authorization'),
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
                    'buttonText' => 'Test POI/PIA configuration',
                    'id' => 'poipiaConfig',
                    'method' => 'poipia',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_POIPIA_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_POIPIA_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_POIPIA_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for POI/PIA transactions
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
        $paymentConfig = new PaymentMethodConfig(PoiPiaTransaction::NAME, $merchantAccountId, $secret);
        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create PoiPiaTransaction
     *
     * @return PoiPiaTransaction
     * @since 1.0.0
     */
    public function createTransaction()
    {
        $transaction = new PoiPiaTransaction();

        return $transaction;
    }
}
