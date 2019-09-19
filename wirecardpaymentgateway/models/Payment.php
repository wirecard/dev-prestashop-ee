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
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
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
    protected $transactionTypes;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $formFields;

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

    /**
     * WirecardPayment constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $context = \Context::getContext();
        $potentialPath = _PS_MODULE_DIR_ . \WirecardPaymentGateway::NAME
                         . '/views/img/paymenttypes/' . static::TYPE . '.png';
        $logoPath = file_exists($potentialPath) ? \Media::getMediaPath($potentialPath) : '';
      
        $this->action_link = $context->link->getModuleLink(
            \WirecardPaymentGateway::NAME,
            'payment',
            [ 'payment_type' => static::TYPE ],
            true
        );

        $this->name = 'Wirecard Payment Processing Gateway';
        $this->transactionTypes = array('authorization', 'capture');
        $this->configuration = new ShopConfigurationService(static::TYPE);

        $this->setAction($this->action_link);
        $this->setLogo($logoPath);
        $this->setModuleName('wd-' . static::TYPE);
        $this->setCallToActionText($this->l($this->configuration->getField('title')));

        //Default back-end operation possibilities
        $this->cancel = array('authorization');
        $this->refund = array('capture-authorization');
        $this->capture = array('authorization');
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
            $templatePath = join(
                DIRECTORY_SEPARATOR,
                [_PS_MODULE_DIR_, \WirecardPaymentGateway::NAME, 'views', 'templates', 'front', static::TYPE  . ".tpl"]
            );

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
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @since 1.0.0
     */
    abstract public function createTransaction($module, $cart, $values, $orderId);
}
