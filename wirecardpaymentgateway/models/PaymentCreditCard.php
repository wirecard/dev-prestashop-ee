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

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use WirecardEE\Prestashop\Helper\TransactionBuilder;

/**
 * Class PaymentCreditCard
 *
 * @extends Payment
 *
 * @since 1.0.0
 */
class PaymentCreditCard extends Payment
{
    /** @var CreditCardTransaction */
    protected $transaction;

    /**
     * PaymentCreditCard constructor.
     *
     * @since 1.0.0
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->transaction = new CreditCardTransaction();
        $this->type = 'creditcard';
        $this->name = 'Wirecard Credit Card';
        $this->formFields = $this->createFormFields();
        $this->setAdditionalInformationTemplate($this->type, $this->setTemplateData());
        $this->setLoadJs(true);

        $this->cancel  = array('authorization');
        $this->capture = array('authorization');
        $this->refund  = array('purchase', 'capture-authorization');
    }

    /**
     * Create form fields for creditcard
     *
     * @return array|null
     * @since 1.0.0
     */
    public function createFormFields()
    {
        return array(
            'tab' => 'CreditCard',
            'fields' => array(
                array(
                    'name' => 'enabled',
                    'label' => $this->l('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->l('enable_heading_title_creditcard'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->l('config_title'),
                    'type' => 'text',
                    'default' => $this->l('heading_title_creditcard'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->l('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->l('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_merchant_account_id',
                    'label'    => $this->l('three_d_merchant_account_id'),
                    'type'     => 'text',
                    'default'  => '508b8896-b37d-4614-845c-26bf8bf2c948',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_secret',
                    'label'       => $this->l('config_three_d_merchant_secret'),
                    'type'        => 'text',
                    'default'     => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'ssl_max_limit',
                    'label'       => $this->l('config_ssl_max_limit'),
                    'type'        => 'text',
                    'default'     => '300.0',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_min_limit',
                    'label'       => $this->l('config_three_d_min_limit'),
                    'type'        => 'text',
                    'default'     => '100.0',
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
                    'name' => 'wpp_url',
                    'label'       => $this->l('config_wpp_url'),
                    'type'        => 'text',
                    'doc' => $this->l('config_wpp_url_desc'),
                    'default'     => 'https://wpp-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'wpp_url',
                    'label'       => $this->l('config_wpp_url'),
                    'type'        => 'text',
                    'doc' => $this->l('config_wpp_url_desc'),
                    'default'     => 'https://wpp-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->l('config_http_user'),
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->l('config_http_password'),
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'pay',
                    'label'   => $this->l('config_payment_action'),
                    'options' => array(
                        array('key' => 'reserve', 'value' => $this->l('text_payment_action_reserve')),
                        array('key' => 'pay', 'value' => $this->l('text_payment_action_pay')),
                    ),
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => $this->l('config_descriptor'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => $this->l('config_additional_info'),
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'ccvault_enabled',
                    'label'=> $this->l('enable_vault'),
                    'type' => 'onoff',
                    'default' => 0
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->l('test_config'),
                    'id' => 'creditcardConfig',
                    'method' => 'creditcard',
                    'send' => array(
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_BASE_URL',
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_HTTP_USER',
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_HTTP_PASS'
                    )
                )
            )
        );
    }

    /**
     * Create config for credit card transactions
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
        $paymentConfig = new CreditCardConfig($merchantAccountId, $secret);

        if ($paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id') !== '') {
            $paymentConfig->setThreeDCredentials(
                $paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id'),
                $paymentModule->getConfigValue($this->type, 'three_d_secret')
            );
        }

        if (is_numeric($paymentModule->getConfigValue($this->type, 'ssl_max_limit')) &&
            $paymentModule->getConfigValue($this->type, 'ssl_max_limit') >= 0) {
            $paymentConfig->addSslMaxLimit(
                new Amount(
                    (float)$paymentModule->getConfigValue($this->type, 'ssl_max_limit'),
                    'EUR'
                )
            );
        }

        if (is_numeric($paymentModule->getConfigValue($this->type, 'three_d_min_limit')) &&
            $paymentModule->getConfigValue($this->type, 'three_d_min_limit') >= 0) {
            $paymentConfig->addThreeDMinLimit(
                new Amount(
                    (float)$paymentModule->getConfigValue($this->type, 'three_d_min_limit'),
                    'EUR'
                )
            );
        }

        $config->add($paymentConfig);

        return $config;
    }

    /**
     * Create request data for credit card ui
     *
     * @param \WirecardPaymentGateway $module
     * @param \Context $context
     * @param int $cartId
     * @return mixed
     * @throws \Exception
     * @since 1.0.0
     */
    public function getRequestData($module, $context, $cartId)
    {
        $baseUrl = $module->getConfigValue($this->type, 'base_url');
        $paymentAction = $module->getConfigValue($this->type, 'payment_action');
        $operation = $this->getOperationForPaymentAction($paymentAction);
        $languageCode = $this->getSupportedHppLangCode($baseUrl, $context);
        $config = $this->createPaymentConfig($module);

        $transactionService = new TransactionService($config);
        $transactionBuilder = new TransactionBuilder($module, $context, $cartId, $this->type);

        // If an order already exists, use that orderId. Otherwise create a new one.
        // This case may happen if one previously selected UnionPay International as payment method.
        $orderId = \Order::getIdByCartId($cartId) ?: $transactionBuilder->createOrder();

        $transactionBuilder->setOrderId($orderId);
        $transaction = $transactionBuilder->buildTransaction();

        return $transactionService->getCreditCardUiWithData($transaction, $operation, $languageCode);
    }

    /**
     * Create creditcard transaction
     *
     * @param \WirecardPaymentGateway $module
     * @param \Cart $cart
     * @param array $values
     * @param int $orderId
     * @return null|CreditCardTransaction
     * @since 1.0.0
     */
    public function createTransaction($module, $cart, $values, $orderId)
    {
        $config = $this->createPaymentConfig($module);

        $this->transaction->setConfig($config->get(CreditCardTransaction::NAME));
        $this->transaction->setTermUrl($module->createRedirectUrl($orderId, $this->type, 'success'));

        return $this->transaction;
    }

    /**
     * Create cancel transaction
     *
     * @param $transactionData
     * @return CreditCardTransaction
     * @since 1.0.0
     */
    public function createCancelTransaction($transactionData)
    {
        $this->transaction->setParentTransactionId($transactionData->transaction_id);
        $this->transaction->setParentTransactionType($transactionData->transaction_type);
        $this->transaction->setAmount(new Amount((float)$transactionData->amount, $transactionData->currency));

        return $this->transaction;
    }

    /**
     * Create refund transaction
     *
     * @param $transactionData
     * @return CreditCardTransaction
     * @since 1.2.5
     */
    public function createRefundTransaction($transactionData)
    {
        return $this->createCancelTransaction($transactionData);
    }

    /**
     * Create pay transaction
     *
     * @param Transaction $transactionData
     * @return CreditCardTransaction
     * @since 1.0.0
     */
    public function createPayTransaction($transactionData)
    {
        $this->transaction->setParentTransactionId($transactionData->transaction_id);
        $this->transaction->setAmount(new Amount((float)$transactionData->amount, $transactionData->currency));

        return $this->transaction;
    }

    /**
     * Set template variables
     *
     * @return array
     * @since 1.0.0
     */
    protected function setTemplateData()
    {
        $test = \Configuration::get(
            sprintf(
                'WIRECARD_PAYMENT_GATEWAY_%s_%s',
                \Tools::strtoupper($this->type),
                \Tools::strtoupper('ccvault_enabled')
            )
        );

        return array('ccvaultenabled' => (bool) $test);
    }

    /**
     * Get supported language code for hpp seamless form renderer
     *
     * @param string $baseUrl
     * @param \Context $context
     * @return mixed|string
     * @since 1.3.3
     */
    protected function getSupportedHppLangCode($baseUrl, $context)
    {
        $isoCode = $context->language->iso_code;
        $languageCode = $context->language->language_code;
        $language = 'en';
        //special case for chinese languages
        switch ($languageCode) {
            case 'zh-tw':
                $isoCode = 'zh_TW';
                break;
            case 'zh-cn':
                $isoCode = 'zh_CN';
                break;
            default:
                break;
        }
        try {
            $supportedLang = json_decode(\Tools::file_get_contents(
                $baseUrl . '/engine/includes/i18n/languages/hpplanguages.json'
            ));
            if (key_exists($isoCode, $supportedLang)) {
                $language = $isoCode;
            }
        } catch (\Exception $exception) {
            return 'en';
        }
        return $language;
    }
}
