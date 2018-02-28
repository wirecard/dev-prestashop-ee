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
 */

use Wirecard\PaymentSdk\Config\Config;

/**
 * Basic Payment class
 *
 * Class Payment
 *
 * @since 1.0.0
 */
class Payment
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $config;

    /**
     * @var \Wirecard\PaymentSdk\Transaction\Transaction
     * @since 1.0.0
     */
    protected $transaction;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $baseUrl;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $httpUser;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $httpPass;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $transactionTypes;

    /**
     * @var array
     * @sine 1.0.0
     */
    protected $formFields;

    /**
     * WirecardPayment constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name = 'Wirecard Payment Processing Gateway';
        $this->transactionTypes = array('authorization', 'capture');
    }

    /**
     * Create config for transaction service
     *
     * @param $baseUrl
     * @param $httpUser
     * @param $httpPass
     * @return array|\Wirecard\PaymentSdk\Config\Config
     *
     * @since 1.0.0
     */
    public function createConfig($baseUrl, $httpUser, $httpPass)
    {
        $this->config = new Config($baseUrl, $httpUser, $httpPass);
        return $this->config;
    }

    /**
     * Get payment name
     *
     * @return string
     * @since 1.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get payment config
     *
     * @return array
     * @since 1.0.0
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get transaction types
     *
     * @return array
     * @since 1.0.0
     */
    public function getTransactionTypes()
    {
        return $this->transactionTypes;
    }

    /**
     * Get form fields for payment settings
     *
     * @return null|array
     * @since 1.0.0
     */
    public function getFormFields()
    {
        return null;
    }

    /**
     * Create redirect Urls
     *
     * @param $paymentState
     * @return null
     * @since 1.0.0
     */
    public function createRedirectUrl($paymentState)
    {
        return null;
    }

    /**
     * Create notification Urls
     *
     * @return null
     * @since 1.0.0
     */
    public function createNotificationUrl()
    {
        return null;
    }
}
