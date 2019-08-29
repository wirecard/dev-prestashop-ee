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

use Configuration;
use Wirecard\Converter\WppVTwoConverter;
use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use WirecardEE\Prestashop\Helper\CurrencyHelper;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
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

    /** @var Logger $logger */
    protected $logger;

    /**
     * PaymentCreditCard constructor.
     * @param \Module $module
     *
     * @since 2.0.0 Add logger
     * @since 1.0.0
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->logger = new WirecardLogger();
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
                    'name'        => 'wpp_url',
                    'label'       => $this->l('config_wpp_url'),
                    'type'        => 'text',
                    'doc'         => $this->l('config_wpp_url_desc'),
                    'default'     => 'https://wpp-test.wirecard.com',
                    'required'    => true,
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
                    'name' => 'requestor_challenge',
                    'type'    => 'select',
                    'default' => ChallengeInd::NO_PREFERENCE,
                    'label'   => $this->l('requestor_challenge'),
                    'options' => array(
                        array(
                            'key'   => ChallengeInd::NO_PREFERENCE,
                            'value' => $this->l('config_challenge_no_preference')
                        ),
                        array(
                            'key'   => ChallengeInd::NO_CHALLENGE,
                            'value' => $this->l('config_challenge_no_challenge')
                        ),
                        array(
                            'key'   => ChallengeInd::CHALLENGE_THREED,
                            'value' => $this->l('config_challenge_challenge_threed')
                        )
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
                        'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_WPP_URL',
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
        $currency = \Context::getContext()->currency;

        $baseUrl  = $paymentModule->getConfigValue($this->type, 'base_url');
        $httpUser = $paymentModule->getConfigValue($this->type, 'http_user');
        $httpPass = $paymentModule->getConfigValue($this->type, 'http_pass');

        $merchantAccountId = $paymentModule->getConfigValue($this->type, 'merchant_account_id');
        $secret = $paymentModule->getConfigValue($this->type, 'secret');

        $config = $this->createConfig($baseUrl, $httpUser, $httpPass);
        $paymentConfig = new CreditCardConfig($merchantAccountId, $secret);
        $currencyConverter = new CurrencyHelper();

        if ($paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id') !== '') {
            $paymentConfig->setThreeDCredentials(
                $paymentModule->getConfigValue($this->type, 'three_d_merchant_account_id'),
                $paymentModule->getConfigValue($this->type, 'three_d_secret')
            );
        }

        if (is_numeric($paymentModule->getConfigValue($this->type, 'ssl_max_limit'))
            && $paymentModule->getConfigValue($this->type, 'ssl_max_limit') >= 0) {
            $paymentConfig->addSslMaxLimit(
                $currencyConverter->getConvertedAmount(
                    $paymentModule->getConfigValue($this->type, 'ssl_max_limit'),
                    $currency->iso_code
                )
            );
        }

        if (is_numeric($paymentModule->getConfigValue($this->type, 'three_d_min_limit'))
            && $paymentModule->getConfigValue($this->type, 'three_d_min_limit') >= 0) {
            $paymentConfig->addThreeDMinLimit(
                $currencyConverter->getConvertedAmount(
                    $paymentModule->getConfigValue($this->type, 'three_d_min_limit'),
                    $currency->iso_code
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
        $paymentAction = $module->getConfigValue($this->type, 'payment_action');
        $operation = $this->getOperationForPaymentAction($paymentAction);
        $languageCode = $this->getSupportedLangCode($context);
        $config = $this->createPaymentConfig($module);

        $transactionService = new TransactionService($config, $this->logger);
        $transactionBuilder = new TransactionBuilder($module, $context, $cartId, $this->type);
        // Set unique cartId as orderId to avoid order creation before payment
        $transactionBuilder->setOrderId($cartId);
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
        $this->transaction->setTermUrl($module->createRedirectUrl($orderId, $this->type, 'success', $cart->id));

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
        $test = Configuration::get(
            sprintf(
                'WIRECARD_PAYMENT_GATEWAY_%s_%s',
                \Tools::strtoupper($this->type),
                \Tools::strtoupper('ccvault_enabled')
            )
        );

        $wppUrl = $this->module->getConfigValue('creditcard', 'wpp_url');
        return array(
            'ccvaultenabled' => (bool) $test,
        );
    }

    /**
     * Get supported language code for seamless form renderer
     *
     * @param \Context $context
     * @return string
     * @since 2.0.0 Exchange hpp with wpp languages
     *              Use lib
     *              Remove $baseUrl param
     * @since 1.3.3
     */
    protected function getSupportedLangCode($context)
    {
        $converter = new WppVTwoConverter();
        $isoCode = $this->stringSuffixToUpperCase(
            $context->language->language_code
        );

        try {
            $converter->init();
            $language = $converter->convert($isoCode);
        } catch (\Exception $exception) {
            $language = 'en';
            $this->logger->error(__METHOD__ . $exception);
        }

        return $language;
    }

    /**
     * Explode a string using the needle
     * Convert last element to uppercase
     * Implode string using the needle
     *
     * @param $string
     * @param string $needle
     * @return string
     *
     * @since 2.0.0
     */
    protected function stringSuffixToUpperCase($string, $needle = '-')
    {
        $explodedString       = explode($needle, $string);
        $lastElement          = end($explodedString);
        $key                  = key($explodedString);
        $explodedString[$key] = mb_strtoupper($lastElement);

        return implode($needle, $explodedString);
    }
}
