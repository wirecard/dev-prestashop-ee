<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use WirecardEE\Prestashop\Classes\Config\Tab\AdminControllerTabConfig;
use WirecardEE\Prestashop\Classes\Constants\ConfigConstants;
use WirecardEE\Prestashop\Classes\Hook\BeforeOrderStatusUpdateHandler;
use WirecardEE\Prestashop\Classes\Hook\OrderStatusUpdateCommand;
use WirecardEE\Prestashop\Classes\Service\OrderStateManagerService;
use WirecardEE\Prestashop\Classes\Service\TabManagerService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Helper\UrlConfigurationChecker;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

/**
 * Class WirecardPaymentGateway
 *
 * @extends PaymentModule
 * @since 1.0.0
 */
class WirecardPaymentGateway extends PaymentModule
{
    use TranslationHelper;

    /**
     * @var string
     * @since 2.1.0
     */
    const NAME = 'wirecardpaymentgateway';

    /**
     * @var string
     * @since 2.0.0
     */
    const VERSION = '2.10.0';

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
     * Payment fields for configuration
     *
     * @var array
     * @since 1.0.0
     */
    private $config;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $html;

    /**
     * @var TabManagerService
     */
    private $tabManagerService;

    /**
     * @var \WirecardEE\Prestashop\Classes\Service\OrderStateManagerService
     */
    private $orderStateManagerService;

    /**
     * WirecardPaymentGateway constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name = self::NAME;
        $this->version = self::VERSION;
        $this->tab = 'payments_gateways';
        $this->author = 'Wirecard';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.4.0', 'max' => '1.7.6.3');
        $this->bootstrap = true;
        $this->controllers = array(
            'payment',
            'validation',
            'notify',
            'return',
            'sepadirectdebit',
            'creditcard'
        );

        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $lang = $this->context->language;
        $this->displayName = $this->getTranslationForLanguage($lang->iso_code, 'module_display_name', $this->name);
        $this->description = $this->getTranslationForLanguage($lang->iso_code, 'module_description', $this->name);
        $this->confirmUninstall = $this->getTranslationForLanguage($lang->iso_code, 'confirm_uninstall', $this->name);

        $this->config = $this->getPaymentFields();
        $this->tabManagerService = new TabManagerService($this->initTabs());
    }

    /**
     * Basic install routine
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @since 1.0.0
     */
    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('actionDispatcher')
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('displayPaymentEU')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('actionPaymentConfirmation')
            || !$this->registerHook('actionUpdateLangAfter')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('postUpdateOrderStatus')
            || !$this->registerHook('updateOrderStatus')
            || !$this->registerHook('actionGetExtraMailTemplateVars')
            || !$this->createTable('tx')
            || !$this->createTable('cc')
            || !$this->setDefaults()) {
            return false;
        }

        // in the case of re-installing the module with a newer version
        if (!$this->addMissingColumns('cc')) {
            return false;
        }

        $this->addUpdateOrderStatuses();

        $this->installTabs();

        $this->addEmailTemplatesToPrestashop();

        return true;
    }

    /**
     * PrestaShop method that will disable and remove the module tabs
     * @param bool $force_all
     * @return bool
     * @since 2.5.0
     */
    public function enable($force_all = false)
    {
        return parent::enable($force_all) &&
            $this->installTabs();
    }

    /**
     * PrestaShop method that will disable and remove the module tabs
     * @param bool $force_all
     * @return bool
     * @since 2.5.0
     */
    public function disable($force_all = false)
    {
        return parent::disable($force_all) &&
            $this->uninstallTabs();
    }

    /**
     * Basic uninstall routine
     *
     * @return bool
     * @since 1.0.0
     */
    public function uninstall()
    {
        if (!$this->deleteConfig()) {
            return false;
        }
        $this->uninstallTabs();

        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Register tabs
     *
     * @since 1.0.0
     * @since 2.5.0 major update logic moved to the TabManagerService
     */
    public function installTabs()
    {
        return $this->tabManagerService->installTabs();
    }

    /**
     * @since 2.5.0 implemented to PrestaShop standard uninstall
     */
    public function uninstallTabs()
    {
        return $this->tabManagerService->uninstallTabs();
    }

    /**
     * Getter for paymentfields from every payment model
     *
     * @return array
     * @since 1.0.0
     */
    public function getPaymentFields()
    {
        $payments = array();

        /** @var \WirecardEE\Prestashop\Models\Payment $payment */
        foreach (PaymentProvider::getPayments() as $payment) {
            array_push($payments, $payment->getFormFields());
        }

        return $payments;
    }

    /**
     * Create content on Wirecard Payment Processing Gateway settings page
     *
     * @return null|string
     * @since 1.0.0
     */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postProcess();
        }

        if (!$this->isUrlConfigurationValid()) {
            $this->html .= $this->displayError($this->getTranslatedString('warning_credit_card_url_mismatch'));
        }

        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path,
                'link' => $this->context->link,
                'ajax_configtest_url' => $this->context->link->getAdminLink('WirecardAjax')
            )
        );
        $this->html .= $this->displayWirecardPaymentGateway();
        $this->html .= $this->renderForm();

        return $this->html;
    }

    /**
     * Get values for configuration fields
     *
     * @return array
     * @since 1.0.0
     */
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getAllConfigurationParameters() as $parameter) {
            $val = Configuration::get($parameter['param_name']);
            if (isset($parameter['multiple']) && $parameter['multiple']) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }
                $x = array();
                if (is_array($val)) {
                    foreach ($val as $v) {
                        $x[$v] = $v;
                    }
                    $pname = $parameter['param_name'] . '[]';
                    $values[$pname] = $x;
                }
            } else {
                $values[$parameter['param_name']] = $val;
            }
        }

        return $values;
    }

    /**
     * Get only non-credential values for configuration fields
     *
     * @return array
     * @since 2.5.1
     */
    public function getNonConfidentialConfigFieldsValues()
    {
        $fields = $this->getConfigFieldsValues();
        $nonConfidentialFieldValues = [];
        foreach ($fields as $fieldKey => $fieldValue) {
            foreach (ConfigConstants::SETTING_SUFFIX_WHITELIST as $whiteListedPrefix) {
                if (strpos($fieldKey, $whiteListedPrefix) !== false) {
                    $nonConfidentialFieldValues[$fieldKey] = $fieldValue;
                    break;
                }
            }
        }

        return $nonConfidentialFieldValues;
    }

    /**
     * Get configuration parameters from config
     *
     * @return array
     * @since 1.0.0
     */
    public function getAllConfigurationParameters()
    {
        $params = array();
        foreach ($this->config as $group) {
            $shopConfigService = new ShopConfigurationService($group['tab']);
            foreach ($group['fields'] as $f) {
                if ('hidden' == $f['type']) {
                    continue;
                }
                $f['param_name'] = $shopConfigService->getFieldName($f['name']);
                $params[] = $f;
            }
        }

        return $params;
    }

    /**
     * Payment options hook
     *
     * @param $params
     * @return array|bool
     * @since 1.0.0
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return false;
        }

        $result = array();

        /** @var \WirecardEE\Prestashop\Models\Payment $paymentMethod */
        foreach (PaymentProvider::getPayments() as $paymentMethod) {
            $shopConfigService = new ShopConfigurationService($paymentMethod::TYPE);

            if ($shopConfigService->getField('enabled') == false) {
                continue;
            }

            if (!$paymentMethod->isAvailable()) {
                continue;
            }

            $result[] = $paymentMethod->toPaymentOption();
        }

        return count($result) ? $result : false;
    }

    /**
     * Create redirect Urls
     *
     * @param $paymentState
     * @return null
     * @since 1.0.0
     */
    public function createRedirectUrl($orderId, $paymentType, $paymentState, $cartId)
    {
        $returnUrl = $this->context->link->getModuleLink(
            $this->name,
            'return',
            array(
                'id_order' => $orderId,
                'payment_type' => $paymentType,
                'payment_state' => $paymentState,
                'id_cart'   => $cartId
            )
        );

        return $returnUrl;
    }

    /**
     * Create notification Urls
     *
     * @return null
     * @since 1.0.0
     */
    public function createNotificationUrl($orderId, $paymentType, $cartId)
    {
        $returnUrl = $this->context->link->getModuleLink(
            $this->name,
            'notify',
            array(
                'id_order' => $orderId,
                'payment_type' => $paymentType,
                'id_cart' => $cartId
            )
        );

        return $returnUrl;
    }

    /**
     * Set the name to the payment type selected
     *
     * @param $params
     * @since 1.0.0
     */
    public function hookActionPaymentConfirmation($params)
    {
        $order = new Order($params['id_order']);
        $this->displayName = $order->payment;
    }

    /**
     * Update “lang” tables after adding or updating a language
     *
     * @since 2.8.0
     */
    public function hookActionUpdateLangAfter()
    {
        $this->addUpdateOrderStatuses();
    }

    /**
     * Display info text for Wirecard Payment Processing Gateway page
     *
     * @return string
     * @since 1.0.0
     */
    protected function displayWirecardPaymentGateway()
    {
        $this->context->smarty->assign(array(
            'shopversion' => _PS_VERSION_,
            'pluginversion' => $this->version,
            'integration' => 'EE',
        ));

        return $this->display(__FILE__, 'infos.tpl');
    }

    /**
     * return available country iso codes
     *
     * @return array
     * @since 1.0.0
     */
    protected function getCountries()
    {
        $cookie = $this->context->cookie;
        $countries = Country::getCountries($cookie->id_lang);
        $ret = array();
        foreach ($countries as $country) {
            $ret[] = array(
                'key' => $country['iso_code'],
                'value' => $country['name']
            );
        }
        return $ret;
    }

    /**
     * return available currency iso codes
     *
     * @return array
     * @since 1.0.0
     */
    protected function getCurrencies()
    {
        $currencies = Currency::getCurrencies();
        $ret = array();
        foreach ($currencies as $currency) {
            $ret[] = array(
                'key' => $currency['iso_code'],
                'value' => $currency['name']
            );
        }
        return $ret;
    }

    /**
     * Save edited configuration values
     *
     * @since 1.0.0
     */
    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);

                if (is_array($val)) {
                    $val = \Tools::jsonEncode($val);
                }
                Configuration::updateValue($parameter['param_name'], $val);
            }
        }
        $this->html .= $this->displayConfirmation($this->getTranslatedString('settings_updated'));
    }

    /**
     * Check if the defined URLs for credit card payments are valid
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isUrlConfigurationValid()
    {
        $shopConfigService = new ShopConfigurationService(CreditCardTransaction::NAME);

        $baseUrl = $shopConfigService->getField('base_url');
        $wppUrl = $shopConfigService->getField('wpp_url');

        return UrlConfigurationChecker::isUrlConfigurationValid($baseUrl, $wppUrl);
    }

    /**
     * Render form including configuration values per payment
     *
     * @return mixed
     * @since 1.0.0
     */
    private function renderForm()
    {
        $radioType = 'switch';

        $radioOptions = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->getTranslatedString('text_enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->getTranslatedString('text_disabled')
            )
        );

        $tempFields = $this->createInputFields($radioType, $radioOptions);
        $inputFields = $tempFields['inputFields'];
        $tabs = $tempFields['tabs'];
        $lang = $this->context->language;

        $fields = array(
            'form' => array(
                'tabs' => $tabs,
                'legend' => array(
                    'title' =>
                        $this->getTranslationForLanguage($lang->iso_code, 'payment_method_settings', $this->name),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputFields,
                'submit' => array(
                    'title' => $this->getTranslationForLanguage($lang->iso_code, 'text_save', $this->name),
                )
            ),
        );

        return $this->createForm($fields);
    }

    /**
     * Create input fields and tabs
     *
     * @param $radioType
     * @param $radioOptions
     * @return array
     * @since 1.0.0
     */
    private function createInputFields($radioType, $radioOptions)
    {
        $input_fields = array();
        $tabs = array();

        foreach ($this->config as $value) {
            $tabname = $value['tab'];
            $tabs[$tabname] = $tabname;
            $shopConfigService = new ShopConfigurationService($tabname);
            foreach ($value['fields'] as $f) {
                if ('hidden' == $f['type']) {
                    continue;
                }
                $elem = array(
                    'name' => $shopConfigService->getFieldName($f['name']),
                    'label' => isset($f['label'])?$this->getTranslatedString($f['label']):'',
                    'tab' => $tabname,
                    'type' => $f['type'],
                    'required' => isset($f['required']) && $f['required']
                );

                if (isset($f['doc'])) {
                    $elem['desc'] = $f['doc'];
                }

                switch ($f['type']) {
                    case 'linkbutton':
                        $elem['buttonText'] = $f['buttonText'];
                        $elem['id'] = $f['id'];
                        $elem['method'] = $f['method'];
                        $elem['send'] = $f['send'];
                        break;

                    case 'text':
                        if (!isset($elem['class'])) {
                            $elem['class'] = 'fixed-width-xl';
                        }

                        if (isset($f['maxchar'])) {
                            $elem['maxlength'] = $elem['maxchar'] = $f['maxchar'];
                        }
                        break;

                    case 'onoff':
                        $elem['type'] = $radioType;
                        $elem['class'] = 't';
                        $elem['is_bool'] = true;
                        $elem['values'] = $radioOptions;
                        break;

                    case 'select':
                        if (isset($f['multiple'])) {
                            $elem['multiple'] = $f['multiple'];
                        }

                        if (isset($f['size'])) {
                            $elem['size'] = $f['size'];
                        }

                        if (isset($f['options'])) {
                            $optfunc = $f['options'];
                            $options = array();
                            if (is_array($optfunc)) {
                                $options = $optfunc;
                            } elseif (method_exists($this, $optfunc)) {
                                $options = $this->$optfunc();
                            }

                            $elem['options'] = array(
                                'query' => $options,
                                'id' => 'key',
                                'name' => 'value'
                            );
                        }
                        break;

                    default:
                        break;
                }

                $input_fields[] = $elem;
            }
        }
        return array('inputFields' => $input_fields, 'tabs' => $tabs);
    }

    /**
     * Create form via HelperFormCore
     *
     * @param $fields
     * @return mixed
     * @since 1.0.0
     */
    private function createForm($fields)
    {
        /** @var HelperFormCore $helper */
        $helper = new HelperForm();
        $helper->show_toolbar = false;

        /** @var LanguageCore $lang */
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields));
    }

    /**
     * Set default configuration values
     *
     * @return bool
     * @since 1.0.0
     */
    private function setDefaults()
    {
        foreach ($this->config as $config) {
            $name = $config['tab'];
            $shopConfigService = new ShopConfigurationService($name);
            foreach ($config['fields'] as $field) {
                if (array_key_exists('default', $field)) {
                    $configParam = $shopConfigService->getFieldName($field['name']);
                    $defValue = $field['default'];
                    if (is_array($defValue)) {
                        $defValue = Tools::jsonEncode($defValue);
                    }

                    if (!Configuration::updateValue($configParam, $defValue)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Delete Configuration values
     *
     * @return bool
     * @since 1.0.0
     */
    private function deleteConfig()
    {
        foreach ($this->config as $config) {
            $name = $config['tab'];
            $shopConfigService = new ShopConfigurationService($name);
            foreach ($config['fields'] as $field) {
                $fieldName = $shopConfigService->getFieldName($field['name']);
                $value = Configuration::get($fieldName);
                if (isset($value)) {
                    if (!Configuration::deleteByName($fieldName)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Create a wirecard table
     *
     * @param string $name
     * @return bool
     * @since 1.0.0
     */
    private function createTable($name)
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `' . _DB_PREFIX_ . 'wirecard_payment_gateway_' .$name .'` (';
        foreach ($this->getColumnDefsTable($name) as $column => $definitions) {
            $sql .= "\n"."\t" . $column . ' ';
            foreach ($definitions as $definition) {
                $sql .= $definition . ' ';
            }
            $sql .= ',';
        }
        $sql .= "\n".'PRIMARY KEY (`' . $name . '_id`)';
        $sql .= "\n" . ') ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Get table columns
     *
     * @param $name
     * @return array
     * @since 1.0.0
     */
    private function getColumnDefsTable($name)
    {
        $defs = array( 'tx' =>
            array(
                "tx_id" => array( "INT(10) UNSIGNED", "NOT NULL", "AUTO_INCREMENT" ),
                "transaction_id" => array( "VARCHAR(36)", "NOT NULL" ),
                "parent_transaction_id" => array( "VARCHAR(36)", "NULL" ),
                "order_id" => array( "INT(10)", "NULL" ),
                "cart_id" => array( "INT(10) UNSIGNED", "NOT NULL" ),
                "ordernumber" => array( "VARCHAR(32)", "NULL" ),
                "paymentmethod" => array( "VARCHAR(32)", "NOT NULL" ),
                "transaction_type" => array( "VARCHAR(32)", "NOT NULL" ),
                "transaction_state" => array( "VARCHAR(32)", "NOT NULL" ),
                "amount" => array( "FLOAT", "NOT NULL" ),
                "currency" => array( "VARCHAR(3)", "NOT NULL" ),
                "response" => array( "TEXT", "NULL" ),
                "created" => array( "DATETIME", "NOT NULL" ),
                "modified" => array( "DATETIME", "NULL" ),
            ),
            'cc' => array(
                "cc_id" => array( "INT(10) UNSIGNED", "NOT NULL", "AUTO_INCREMENT" ),
                "user_id" => array( "INT(10)", "NOT NULL" ),
                "token" => array( "VARCHAR(20)", "NOT NULL", "UNIQUE" ),
                "address_id" => array( "INT(10)", "NULL" ),
                "masked_pan" => array( "VARCHAR(30)", "NOT NULL" ),
                "date_add" => array("DATETIME", "NULL"),
                "date_last_used" => array("DATETIME", "NULL")
            ) );

        return $defs[$name];
    }

    /**
     * Add missing columns
     *
     * @param $name
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @since 1.3.5
     */
    public function addMissingColumns($name)
    {
        $table = '`' . _DB_PREFIX_ . 'wirecard_payment_gateway_' . $name . '`';
        $columns_db = Db::getInstance()->executeS("SHOW COLUMNS FROM $table");
        $columns    = [];

        $column_definitions = $this->getColumnDefsTable($name);

        $sql = null;
        if (is_array($columns_db)) {
            $sql = "ALTER TABLE $table";
            foreach ($columns_db as $column) {
                $columns[] = $column['Field'];
            }

            foreach ($column_definitions as $column => $definitions) {
                if (!in_array($column, $columns)) {
                    $sql .= "\n" . 'ADD COLUMN `' . $column . '` ';
                    foreach ($definitions as $definition) {
                        $sql .= $definition . ' ';
                    }
                    $sql = rtrim($sql, " ");
                    $sql .= ',';
                }
            }

            $sql = rtrim($sql, ",") . ";";
        }

        return $sql == null ? true : $this->executeSql($sql);
    }

    /**
     * Hook for media setter
     *
     * @return bool
     * @since 1.0.0
     */
    public function hookActionFrontControllerSetMedia()
    {
        $creditCardConfig = new ShopConfigurationService(PaymentCreditCard::TYPE);
        $wppUrl = $creditCardConfig->getField('wpp_url');
        $ccVaultEnabled = $creditCardConfig->getField('ccvault_enabled');

        $link = new Link;
        $ccControllerUrl = $link->getModuleLink(
            'wirecardpaymentgateway',
            'creditcard'
        );

        Media::addJsDef(
            array(
                'ccControllerUrl' => $ccControllerUrl,
                'ccVaultEnabled' => $ccVaultEnabled,
                'cartId' => $this->context->cart->id,
            )
        );

        $this->context->controller->registerStylesheet(
            'wd-css',
            'modules/' . $this->name . '/views/css/app.css'
        );

        $this->context->controller->registerJavascript(
            'wd-wpp',
            $wppUrl . '/loader/paymentPage.js',
            array('server' => 'remote', 'position' => 'top', 'priority' => 1)
        );

        foreach (PaymentProvider::getPayments() as $paymentMethod) {
            if (!$paymentMethod->getLoadJs()) {
                continue;
            }

            $this->context->controller->registerJavaScript(
                'wd-js-' . $paymentMethod->getType(),
                'modules/' . $this->name . '/views/js/' . $paymentMethod->getType() . '.js'
            );
        }

        return true;
    }

    /**
     * Show the payment information for PIA
     *
     * @param array $params
     * @return string
     * @since 1.0.0
     */
    public function hookOrderConfirmation($params)
    {
        if ($this->context->cookie->__get('pia-enabled')) {
            $currency = new Currency($params['order']->id_currency);
            $this->context->smarty->assign(
                array(
                    'amount' => $params['order']->total_paid,
                    'iban' => $this->context->cookie->__get('pia-iban'),
                    'bic' => $this->context->cookie->__get('pia-bic'),
                    'refId' => $this->context->cookie->__get('pia-reference-id'),
                    'currency' => $currency->iso_code
                )
            );
            return $this->display(
                __FILE__,
                DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' .
                DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . 'pia.tpl'
            );
        }
    }

    /**
     * Hook for registering new functions to smarty
     *
     * @since 1.3.4
     */
    public function hookActionDispatcher()
    {
        $this->context->smarty->assign('base_url', Context::getContext()->shop->getBaseURL(true));
        $this->context->smarty->registerPlugin('function', 'lFallback', array('WirecardPaymentGateway', 'lFallback'));
    }

    /**
     * Hook called before the status of an order changes.
     * @param array $params
     * @throws Exception
     * @since 2.5.0
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $orderStatusUpdateCommand = new OrderStatusUpdateCommand(
            $params['newOrderStatus'],
            $params['id_order']
        );
        $orderStatusPostUpdateHandler = new BeforeOrderStatusUpdateHandler(
            _PS_OS_SHIPPING_,
            $orderStatusUpdateCommand
        );
        $orderStatusPostUpdateHandler->handle();
    }

    /**
     * Return the translation for a string given a language iso code 'en' 'fr' ..
     *
     * @param string $iso_lang language iso code
     * @param string $key key to translate
     * @param string $file_name file name without extension
     *
     * @return string translation
     * @since 1.3.4
     */
    public static function getTranslationForLanguage($iso_lang, $key, $file_name)
    {
        $file = dirname(__FILE__).'/translations/'.$iso_lang.'.php';
        if (!file_exists($file)) {
            $file = dirname(__FILE__).'/translations/en.php';
        }

        global $_MODULE;
        include($file);
        $hashed_key = md5($key);
        $translation_key = '<{'.'wirecardpaymentgateway'.'}prestashop>'.\Tools::strtolower($file_name).'_'.$hashed_key;

        if (isset($_MODULE[$translation_key]) && mb_strlen($_MODULE[$translation_key]) > 0) {
            return $_MODULE[$translation_key];
        } else {
            return $key;
        }
    }

    /**
     * Translation function for tpl files (called by smarty)
     *
     * @param array $params parameter of the smarty function
     * @param class $smarty smarty object
     *
     * @return string translation
     * @since 1.3.4
     */
    public static function lFallback($params, $smarty)
    {
        if (!isset($params['mod'])) {
            $params['mod'] = false;
        }
        if (!isset($params['sprintf'])) {
            $params['sprintf'] = array();
        }

        $key = $params['s'];
        $basename = basename($smarty->source->name, '.tpl');

        $translation = Translate::postProcessTranslation(
            Translate::getModuleTranslation(
                $params['mod'],
                $key,
                $basename,
                $params['sprintf']
            ),
            $params
        );

        if ($translation === $key) {
            $translation = WirecardPaymentGateway::getTranslationForLanguage('en', $key, $basename);
        }

        return html_entity_decode($translation);
    }

    /**
     * execute sql statement, return true on success, throws exception on error
     * optionally ingore an error code
     *
     * @param $sql
     * @param null|int $ignore
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @since 1.3.5
     */
    public function executeSql($sql, $ignore = null)
    {
        $result = Db::getInstance()->execute($sql);
        if ($result === false && Db::getInstance()->getNumberError() !== $ignore) {
            throw new PrestaShopDatabaseException(Db::getInstance()->getMsgError());
        }

        return true;
    }

    /**
     * Returns an array of AdminControllerTabConfig
     * @return array
     * @since 2.5.0
     */
    private function initTabs()
    {
        //Transaction Overview tab on the side menu
        $tabsConfig[] = new AdminControllerTabConfig(
            $this->name,
            'heading_title_transaction_details',
            'WirecardTransactions',
            'payment',
            1,
            'SELL'
        );

        //Contact support tab in the module settings
        $tabsConfig[] = new AdminControllerTabConfig(
            $this->name,
            'heading_title_support',
            'WirecardSupport'
        );

        $tabsConfig[] = new AdminControllerTabConfig(
            $this->name,
            'heading_title_ajax',
            'WirecardAjax'
        );

        //General tab setting in the module settings
        $tabsConfig[] = new AdminControllerTabConfig(
            $this->name,
            'heading_title_general_settings',
            'WirecardGeneralSettings'
        );

        return $tabsConfig;
    }

    /**
     *  Add or update (if exists) order statuses
     */
    public function addUpdateOrderStatuses()
    {
        $orderManager = new OrderManager();
        $translationKeys = $orderManager::ORDER_STATE_TRANSLATION_KEY_MAP;
        foreach (array_keys($translationKeys) as $translationKey) {
            //@TODO for partial add email send and template
            $orderManager->createOrderState($translationKey);
        }
    }

    /**
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException
     * @since 2.10.0
     */
    public function orderStateManager()
    {
        if (is_null($this->orderStateManagerService)) {
            $this->orderStateManagerService = new OrderStateManagerService();
        }

        return $this->orderStateManagerService;
    }

    /**
     * @since 2.10.0
     */
    public function addEmailTemplatesToPrestashop() {
        $languages = $this->context->controller->getLanguages();
        $fileExtensions = ['html', 'txt'];
        $emailTemplates = ['partially_captured_template', 'partially_refunded_template'];

        foreach ($emailTemplates as $emailTemplate) {
            foreach ($languages as $language) {
                foreach ($fileExtensions as $fileExtension) {
                    $mailTemplatePath = _PS_MODULE_DIR_ . self::NAME . '/' . 'mails' . '/' . $language['iso_code'] . '/' . $emailTemplate . '.' . $fileExtension;
                    if (file_exists($mailTemplatePath)) {
                        \Tools::copy(
                            $mailTemplatePath,
                            _PS_MAIL_DIR_ . $language['iso_code'] . '/' . $emailTemplate . '.' . $fileExtension
                        );
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $params
     */
    public function hookActionGetExtraMailTemplateVars($params) {
        if ($params['template'] === 'partially_refunded_template') {
            //get the order from session to get the partial payment amount
            $params['extra_template_vars']['{my_email_variable}'] = '123124123141245124512451245';
        }
    }
}
