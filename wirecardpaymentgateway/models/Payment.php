<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use WirecardEE\Prestashop\Classes\Config\Credentials\CredentialsConfiguration;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\TemplateHelper;
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

    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = "";

    /**
     * @var array
     * @since 2.0.0
     */
    const OPERATION_MAP = [
        'pay' => 'purchase',
        'reserve' => 'authorization',
    ];

    const CREDENTIALS_FILE_NAME = "credentials_config.xml";

    /**
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $type;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $formFields;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $loadJs;

    /**
     * @var ShopConfigurationService $configuration
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $action_link;

    /** @var CredentialsConfiguration */
    protected $credentialsConfig;

    /** @var WirecardLogger $logger */
    protected $logger;

    /** @var \Customer $customer */
    protected $customer;

    /**
     * WirecardPayment constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $context = \Context::getContext();
        $modulePath = _PS_MODULE_DIR_ . \WirecardPaymentGateway::NAME;
        $potentialPath = $modulePath . '/views/img/paymenttypes/' . static::TYPE . '.png';
        $logoPath = file_exists($potentialPath) ? \Media::getMediaPath($potentialPath) : '';
      
        $this->action_link = $context->link->getModuleLink(
            \WirecardPaymentGateway::NAME,
            'payment',
            [ 'payment_type' => static::TYPE ],
            true
        );

        $this->name = 'Wirecard Payment Processing Gateway';
        $this->type = static::TYPE;
        $this->transactionTypes = array('authorization', 'capture');
        $this->configuration = new ShopConfigurationService(static::TYPE);

        $this->setAction($this->action_link);
        $this->setLogo($logoPath);
        $this->setModuleName('wd-' . static::TYPE);
        $this->setCallToActionText($this->getTranslatedString($this->configuration->getField('title')));
        $this->customer = $context->customer;

        $this->logger = new WirecardLogger();
        $this->credentialsConfig = CredentialsConfiguration::getInstance(
            $modulePath . DIRECTORY_SEPARATOR . self::CREDENTIALS_FILE_NAME,
            $this->logger
        )->getConfigByPaymentMethod($this->getType());
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
    protected function getFormTemplateData()
    {
        return array();
    }

    /**
     * Get the template with its appropriate data
     *
     * @return bool|string
     * @since 1.0.0
     */
    public function getFormTemplateWithData()
    {
        try {
            $templatePath = TemplateHelper::getFrontendTemplatePath(static::TYPE);

            if (!file_exists($templatePath)) {
                return false;
            }

            $context = \Context::getContext();
            $context->smarty->assign(array_merge(
                $this->getFormTemplateData(),
                [ 'action_link' => $this->action_link ]
            ));

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
     * Check if payment is available for specific cart content default true
     *
     * @return bool
     * @since 1.0.0
     */
    public function isAvailable()
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
     * Turn our custom PaymentOption into one that is compatible with PrestaShop
     *
     * @return PaymentOption
     * @since 2.1.0
     */
    public function toPaymentOption()
    {
        $paymentOption = (new PaymentOption());
        $paymentOption->setAction($this->getAction());
        $paymentOption->setLogo($this->getLogo());
        $paymentOption->setModuleName($this->getModuleName());
        $paymentOption->setCallToActionText($this->getCallToActionText());
        $paymentOption->setForm($this->getFormTemplateWithData());

        return $paymentOption;
    }

    /**
     * Create Default Transaction
     *
     * @param string $operation
     * @since 1.0.0
     */
    abstract public function createTransaction($operation = null);

    /**
     * @param string $operation
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     * @since 2.4.0
     */
    abstract public function createTransactionInstance($operation = null);

    /**
     * Returns an array of Entities that are mandatory for the payment method post processing transactions
     *
     * @return array
     * @since 2.5.0
     */
    public function getPostProcessingMandatoryEntities()
    {
        return [];
    }
}
