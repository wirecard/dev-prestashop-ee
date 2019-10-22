<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\Converter\WppVTwoConverter;
use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
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
    /**
     * @var string
     * @since 2.1.0
     */
    const TYPE = CreditCardTransaction::NAME;

    /**
     * @var string
     * @since 2.1.0
     */
    const TRANSLATION_FILE = "paymentcreditcard";

    /** @var CreditCardTransaction */
    protected $transaction;

    /** @var WirecardLogger $logger */
    protected $logger;

    /**
     * PaymentCreditCard constructor.
     * @param \Module $module
     *
     * @since 2.0.0 Add logger
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->logger = new WirecardLogger();
        $this->type = self::TYPE;
        $this->name = 'Wirecard Credit Card';
        $this->formFields = $this->createFormFields();
        $this->setLoadJs(true);
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
                    'label' => $this->getTranslatedString('text_enable'),
                    'type' => 'onoff',
                    'doc' => $this->getTranslatedString('enable_heading_title_creditcard'),
                    'default' => 0,
                ),
                array(
                    'name' => 'title',
                    'label' => $this->getTranslatedString('config_title'),
                    'type' => 'text',
                    'default' => $this->getTranslatedString('heading_title_creditcard'),
                    'required' => true,
                ),
                array(
                    'name' => 'merchant_account_id',
                    'label'   => $this->getTranslatedString('config_merchant_account_id'),
                    'type'    => 'text',
                    'default' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
                    'required' => true,
                ),
                array(
                    'name' => 'secret',
                    'label'   => $this->getTranslatedString('config_merchant_secret'),
                    'type'    => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_merchant_account_id',
                    'label'    => $this->getTranslatedString('three_d_merchant_account_id'),
                    'type'     => 'text',
                    'default'  => '508b8896-b37d-4614-845c-26bf8bf2c948',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_secret',
                    'label'       => $this->getTranslatedString('config_three_d_merchant_secret'),
                    'type'        => 'text',
                    'default'     => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                ),
                array(
                    'name' => 'ssl_max_limit',
                    'label'       => $this->getTranslatedString('config_ssl_max_limit'),
                    'type'        => 'text',
                    'default'     => '300.0',
                    'required' => true,
                ),
                array(
                    'name' => 'three_d_min_limit',
                    'label'       => $this->getTranslatedString('config_three_d_min_limit'),
                    'type'        => 'text',
                    'default'     => '100.0',
                    'required' => true,
                ),
                array(
                    'name' => 'base_url',
                    'label'       => $this->getTranslatedString('config_base_url'),
                    'type'        => 'text',
                    'doc' => $this->getTranslatedString('config_base_url_desc'),
                    'default'     => 'https://api-test.wirecard.com',
                    'required' => true,
                ),
                array(
                    'name'        => 'wpp_url',
                    'label'       => $this->getTranslatedString('config_wpp_url'),
                    'type'        => 'text',
                    'doc'         => $this->getTranslatedString('config_wpp_url_desc'),
                    'default'     => 'https://wpp-test.wirecard.com',
                    'required'    => true,
                ),
                array(
                    'name' => 'http_user',
                    'label'   => $this->getTranslatedString('config_http_user'),
                    'type'    => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                ),
                array(
                    'name' => 'http_pass',
                    'label'   => $this->getTranslatedString('config_http_password'),
                    'type'    => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                ),
                array(
                    'name' => 'payment_action',
                    'type'    => 'select',
                    'default' => 'pay',
                    'label'   => $this->getTranslatedString('config_payment_action'),
                    'options' => array(
                        array('key' => 'reserve', 'value' => $this->getTranslatedString('text_payment_action_reserve')),
                        array('key' => 'pay', 'value' => $this->getTranslatedString('text_payment_action_pay')),
                    ),
                ),
                array(
                    'name' => 'requestor_challenge',
                    'type'    => 'select',
                    'default' => ChallengeInd::NO_PREFERENCE,
                    'label'   => $this->getTranslatedString('config_challenge_indicator'),
                    'options' => array(
                        array(
                            'key'   => ChallengeInd::NO_PREFERENCE,
                            'value' => $this->getTranslatedString('config_challenge_no_preference')
                        ),
                        array(
                            'key'   => ChallengeInd::NO_CHALLENGE,
                            'value' => $this->getTranslatedString('config_challenge_no_challenge')
                        ),
                        array(
                            'key'   => ChallengeInd::CHALLENGE_THREED,
                            'value' => $this->getTranslatedString('config_challenge_challenge_threed')
                        )
                    ),
                ),
                array(
                    'name' => 'descriptor',
                    'label'   => $this->getTranslatedString('config_descriptor'),
                    'type'    => 'onoff',
                    'default' => 0,
                ),
                array(
                    'name' => 'send_additional',
                    'label'   => $this->getTranslatedString('config_additional_info'),
                    'type'    => 'onoff',
                    'default' => 1,
                ),
                array(
                    'name' => 'ccvault_enabled',
                    'label'=> $this->getTranslatedString('enable_vault'),
                    'type' => 'onoff',
                    'default' => 0
                ),
                array(
                    'name' => 'test_credentials',
                    'type' => 'linkbutton',
                    'required' => false,
                    'buttonText' => $this->getTranslatedString('test_config'),
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
        $paymentAction = $this->configuration->getField('payment_action');
        $operation = $this->getOperationForPaymentAction($paymentAction);
        $languageCode = $this->getSupportedLangCode($context);
        $config = (new PaymentConfigurationFactory($this->configuration))->createConfig();

        $transactionService = new TransactionService($config, $this->logger);
        $transactionBuilder = new TransactionBuilder($this->type);
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
    public function createTransaction($operation = null)
    {
        $module = \Module::getInstanceByName(\WirecardPaymentGateway::NAME);
        $context = \Context::getContext();
        $cart = $context->cart;
        $orderId = \Order::getIdByCartId($cart->id);

        $config = (new PaymentConfigurationFactory($this->configuration))->createConfig();
        $paymentConfig = $config->get(CreditCardTransaction::NAME);

        $transaction = $this->createTransactionInstance($operation);
        $transaction->setConfig($paymentConfig);
        $transaction->setTermUrl($module->createRedirectUrl($orderId, $this->type, 'success', $cart->id));

        return $transaction;
    }

    /**
     * Get a clean transaction instance for this payment type.
     *
     * @param string $operation
     * @return CreditCardTransaction
     * @since 2.4.0
     */
    public function createTransactionInstance($operation = null)
    {
        return new CreditCardTransaction();
    }

    /**
     * Set required variables for template
     *
     * @return array
     * @since 2.1.0 Change method name and use new configuration
     * @since 1.0.0
     */
    protected function getFormTemplateData()
    {
        $ccVaultEnabled = $this->configuration->getField('ccvault_enabled');

        return array(
            'ccvaultenabled' => (bool) $ccVaultEnabled,
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
        $isoCode = $this->removeSuffix(
            mb_strtolower($context->language->language_code)
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
     * Removes the suffix of ISO codes after a certain cut off point.
     *
     * @param string $isoCode
     * @param string $cutOffPoint
     * @return string
     * @since 2.2.2
     */
    protected function removeSuffix($isoCode, $cutOffPoint = '-')
    {
        $trimmed = mb_substr($isoCode, 0, mb_strpos($isoCode, $cutOffPoint));

        return mb_strlen($trimmed) > 0
            ? $trimmed
            : $isoCode;
    }
}
