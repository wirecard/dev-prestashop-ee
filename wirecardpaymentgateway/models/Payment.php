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
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Config\SepaConfig;
use WirecardEE\Prestashop\Helper\PaymentConfiguration;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Basic Payment class
 *
 * Class Payment
 *
 * @since 1.0.0
 */
abstract class Payment extends PaymentOption
{
    use TranslationHelper;

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
        $context = \Context::getContext();
        $logoPath = \Media::getMediaPath(
            _PS_MODULE_DIR_ . \WirecardPaymentGateway::NAME . '/views/img/paymenttypes/' . static::TYPE . '.png'
        );
        $actionLink = $context->link->getModuleLink(
            \WirecardPaymentGateway::NAME,
            'payment',
            [ 'paymentType' => static::TYPE ],
            true
        );

        $this->name = 'Wirecard Payment Processing Gateway';
        $this->transactionTypes = array('authorization', 'capture');
        $this->configuration = new PaymentConfiguration(static::TYPE);

        $this->setAction($actionLink);
        $this->setLogo($logoPath);
        $this->setModuleName('wd-' . static::TYPE);
        $this->setCallToActionText($this->l($this->configuration->getField('title')));
        $this->setForm($this->getFormTemplateWithData());

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
        $maid = $this->configuration->getField('merchant_account_id');
        $secret = $this->configuration->getField('secret');

        $this->sdkConfiguration = new Config(
            $this->configuration->getField('base_url'),
            $this->configuration->getField('http_user'),
            $this->configuration->getField('http_pass')
        );

        $this->sdkConfiguration->setShopInfo(self::SHOP_NAME, _PS_VERSION_);
        $this->sdkConfiguration->setPluginInfo(self::EXTENSION_HEADER_PLUGIN_NAME, \WirecardPaymentGateway::VERSION);

        switch (static::TYPE) {
            case 'creditcard':
                $paymentMethodConfig = new CreditCardConfig($maid, $secret);
                break;
            case 'sepacredittransfer':
            case 'sepadirectdebit':
                $paymentMethodConfig = new SepaConfig(static::TYPE, $maid, $secret);
                break;
            default:
                $paymentMethodConfig = new PaymentMethodConfig(static::TYPE, $maid, $secret);
                break;
        }

        $this->sdkConfiguration->add($paymentMethodConfig);

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
     * Get the template data required for rendering the payment method form
     *
     * @return array
     * @since 1.0.0
     */
    protected function getFormTemplateData() {
        return array();
    }


    /**
     * Get the template back
     *
     * @return bool|string
     * @since 1.0.0
     */
    public function getFormTemplateWithData()
    {
        try {
            $templatePath = join(
                DIRECTORY_SEPARATOR,
                [_PS_MODULE_DIR_, \WirecardPaymentGateway::NAME, 'views', 'templates', 'front', static::TYPE . ".tpl"]
            );

            $context = \Context::getContext();
            $context->smarty->assign($this->getFormTemplateData());

            return $context->smarty->fetch($templatePath);
        } catch (\SmartyException $e) {
            return false;
        }
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

    public function toPaymentOption() {
        $paymentOption = (new PaymentOption());
        $paymentOption->setAction($this->getAction());
        $paymentOption->setLogo($this->getLogo());
        $paymentOption->setModuleName($this->getModuleName());
        $paymentOption->setCallToActionText($this->getCallToActionText());
        $paymentOption->setForm($this->getForm());

        return $paymentOption;
    }

    /**
     * Create Default Transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @since 1.0.0
     */
    abstract public function createTransaction($module, $cart, $values, $orderId);
}
