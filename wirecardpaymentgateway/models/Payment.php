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

/**
 * Basic Payment class
 *
 * Class Payment
 */
class Payment
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Wirecard\PaymentSdk\Transaction\Transaction
     */
    protected $transaction;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $httpUser;

    /**
     * @var string
     */
    protected $httpPass;

    /**
     * @var array
     */
    protected $transactionTypes;

    /**
     * @var array
     */
    protected $formFields;

    /**
     * WirecardPayment constructor.
     */
    public function __construct()
    {
        $this->name = 'Wirecard Payment Processing Gateway';
        $this->transactionTypes = array('authorization', 'capture');
    }

    /**
     * @param $baseUrl
     * @param $httpUser
     * @param $httpPass
     * @return array|\Wirecard\PaymentSdk\Config\Config
     */
    public function createConfig($baseUrl, $httpUser, $httpPass)
    {
        $this->config = new \Wirecard\PaymentSdk\Config\Config($baseUrl, $httpUser, $httpPass);
        return $this->config;
    }

    /**
     * Get payment name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get payment config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get transaction types
     *
     * @return array
     */
    public function getTransactionTypes()
    {
        return $this->transactionTypes;
    }

    /**
     * Get form fields for payment settings
     *
     * @return null|array
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
     */
    public function createRedirectUrl($paymentState)
    {
        return null;
    }

    /**
     * Create notification Urls
     *
     * @return null
     */
    public function createNotificationUrl()
    {
        return null;
    }
}
