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

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use WirecardEE\Prestashop\Helper\PaymentConfiguration;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Basic Payment class
 *
 * Class Payment
 *
 * @since 1.0.0
 */
class Payment extends PaymentOption
{
    const TYPE = "";

    /**
     * @var array
     * @since 2.0.0
     */
    const OPERATION_MAP = [
        'pay' => 'purchase',
        'reserve' => 'authorization',
    ];

    /**
     * @var string
     * @since 2.0.0
     */
    const SHOP_NAME = 'Prestashop';

    /**
     * @var string
     * @since 2.0.0
     */
    const EXTENSION_HEADER_PLUGIN_NAME = 'prestashop-ee+Wirecard';

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var Config
     * @since 1.0.0
     */
    protected $sdkConfiguration;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $type;

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
     * @since 1.0.0
     */
    protected $formFields;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $additionalInformationTemplate;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $cancel;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $refund;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $capture;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $templateData;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $loadJs;

    /**
     * @var PaymentConfiguration $configuration
     */
    protected $configuration;


    /**
     * WirecardPayment constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name = 'Wirecard Payment Processing Gateway';
        $this->transactionTypes = array('authorization', 'capture');
        $this->configuration = new PaymentConfiguration(self::TYPE);

        //Default back-end operation possibilities
        $this->cancel = array('authorization');
        $this->refund = array('capture-authorization');
        $this->capture = array('authorization');
    }

    /**
     * Create config for transaction service
     *
     * @param $baseUrl
     * @param $httpUser
     * @param $httpPass
     * @return Config
     * @since 1.0.0
     */
    public function createConfig()
    {
        $this->sdkConfiguration = new Config(
            $this->configuration->getField('base_url'),
            $this->configuration->getField('http_user'),
            $this->configuration->getField('http_pass')
        );

        $this->sdkConfiguration->setShopInfo(self::SHOP_NAME, _PS_VERSION_);
        $this->sdkConfiguration->setPluginInfo(self::EXTENSION_HEADER_PLUGIN_NAME, \WirecardPaymentGateway::VERSION);

        $this->sdkConfiguration->add(new PaymentMethodConfig(
           self::TYPE,
           $this->configuration->getField('merchant_account_id'),
           $this->configuration->getField('secret')
        ));

        return $this->sdkConfiguration;
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
     * @return Config
     * @since 1.0.0
     */
    public function getConfig()
    {
        return $this->sdkConfiguration;
    }

    /**
     * Get payment type
     *
     * @return string
     * @since 1.0.0
     */
    public function getType()
    {
        return $this->type;
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
        return $this->formFields;
    }

    /**
     * Create Default Transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        return null;
    }

    /**
     * Set a template to display additional information
     *
     * @param $template
     * @since 1.0.0
     */
    public function setAdditionalInformationTemplate($template, $data = null)
    {
        $this->additionalInformationTemplate = 'wirecardpaymentgateway'. DIRECTORY_SEPARATOR . 'views' .
            DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . $template;

        if ($data != null) {
            $this->templateData = $data;
        }
    }

    /**
     * Get the template back
     *
     * @return bool|string
     * @since 1.0.0
     */
    public function getAdditionalInformationTemplate()
    {
        if (isset($this->additionalInformationTemplate)) {
            return $this->additionalInformationTemplate;
        } else {
            return false;
        }
    }

    /**
     * Check if payment method can use capture
     *
     * @param string $type
     * @return bool
     * @since 1.0.0
     */
    public function canCapture($type)
    {
        if ($this->capture && in_array($type, $this->capture)) {
            return true;
        }

        return false;
    }

    /**
     * Check if payment method can use cancel
     *
     * @param string $type
     * @return boolean
     * @since 1.0.0
     */
    public function canCancel($type)
    {
        if ($this->cancel && in_array($type, $this->cancel)) {
            return true;
        }

        return false;
    }

    /**
     * Check if payment method can use refund
     *
     * @param string $type
     * @return boolean
     * @since 1.0.0
     */
    public function canRefund($type)
    {
        if ($this->refund && in_array($type, $this->refund)) {
            return true;
        }

        return false;
    }

    /**
     * Get the template data back
     *
     * @return bool|array
     * @since 1.0.0
     */
    public function getTemplateData()
    {
        if (isset($this->templateData)) {
            return $this->templateData;
        } else {
            return false;
        }
    }

    /**
     * Set loadJs
     *
     * @param bool $load
     * @since 1.0.0
     */
    public function setLoadJs($load)
    {
        $this->loadJs = $load;
    }

    /**
     * Check if js should be loaded
     *
     * @return bool
     * @since 1.0.0
     */
    public function getLoadJs()
    {
        return isset($this->loadJs) ? $this->loadJs : false;
    }

    /**
     * Check if payment is available for specific cart content default true
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @return bool
     * @since 1.0.0
     */
    public function isAvailable($module, $cart)
    {
        return true;
    }

    /**
     * Maps from TransactionService values to proper operations.
     *
     * @param $action
     * @return mixed
     * @since 2.0.0
     */
    public function getOperationForPaymentAction($action)
    {
        if (key_exists($action, self::OPERATION_MAP)) {
            return self::OPERATION_MAP[$action];
        }

        return $action;
    }

    /**
     * Translation function for payment classes
     *
     * @param string $key
     * @return string
     * @since 1.3.4
     */
    protected function l($key)
    {
        $module = \Module::getInstanceByName(\WirecardPaymentGateway::NAME);

        return $module->l($key, $this->getClassNameLower());
    }

    /**
     * Returns the lower case class name of the child class
     *
     * @return string
     * @since 1.3.4
     */
    private function getClassNameLower()
    {
        $class = get_class($this);
        return \Tools::strtolower(\Tools::substr($class, strrpos($class, '\\') + 1));
    }
}
