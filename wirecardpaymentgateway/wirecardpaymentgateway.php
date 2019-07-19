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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use WirecardEE\Prestashop\Helper\UrlConfigurationChecker;
use WirecardEE\Prestashop\Models\PaymentCreditCard;
use WirecardEE\Prestashop\Models\PaymentIdeal;
use WirecardEE\Prestashop\Models\PaymentPaypal;
use WirecardEE\Prestashop\Models\PaymentSepaDirectDebit;
use WirecardEE\Prestashop\Models\PaymentSepaCreditTransfer;
use WirecardEE\Prestashop\Models\PaymentSofort;
use WirecardEE\Prestashop\Models\PaymentPoiPia;
use WirecardEE\Prestashop\Models\PaymentAlipayCrossborder;
use WirecardEE\Prestashop\Models\PaymentPtwentyfour;
use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Models\PaymentMasterpass;
use WirecardEE\Prestashop\Models\PaymentUnionPayInternational;
use WirecardEE\Prestashop\Helper\OrderManager;

define('IS_CORE', false);

/**
 * Class WirecardPaymentGateway
 *
 * @extends PaymentModule
 * @since 1.0.0
 */
class WirecardPaymentGateway extends PaymentModule
{
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
     * WirecardPaymentGateway constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        require_once(_PS_MODULE_DIR_.'wirecardpaymentgateway'.DIRECTORY_SEPARATOR.'vendor'.
            DIRECTORY_SEPARATOR.'autoload.php');

        $this->name = 'wirecardpaymentgateway';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0';
        $this->author = 'Wirecard';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => '1.7.5.2');
        $this->bootstrap = true;
        $this->controllers = array(
            'payment',
            'validation',
            'notify',
            'return',
            'configprovider',
            'sepadirectdebit',
            'creditcard'
        );

        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('module_display_name');
        $this->description = $this->l('module_description');
        $this->confirmUninstall = $this->l('confirm_uninstall');

        $this->config = $this->getPaymentFields();
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
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->createTable('tx')
            || !$this->createTable('cc')
            || !$this->setDefaults()) {
            return false;
        }

        // in the case of re-installing the module with a newer version
        if (!$this->addMissingColumns('cc')) {
            return false;
        }

        $orderManager = new OrderManager($this);
        $orderManager->createOrderState(OrderManager::WIRECARD_OS_AUTHORIZATION);
        $orderManager->createOrderState(OrderManager::WIRECARD_OS_AWAITING);
        $orderManager->createOrderState(OrderManager::WIRECARD_OS_STARTING);

        $this->installTabs();

        return true;
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
     */
    public function installTabs()
    {
        $key = $this->l('heading_title_transaction_details');
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'WirecardTransactions';
        $tab->name = array();
        $tab->name[1] = $key;
        foreach (Language::getLanguages(false) as $lang) {
            $translated_string = $this->getTranslationForLanguage(
                $lang['iso_code'],
                $key,
                $this->name
            );
            $tab->name[$lang['id_lang']] = $translated_string !== $key ?
                $translated_string : $tab->name[1];
        }
        $tab->module = $this->name;
        $tab->add();

        $key = $this->l('heading_title_support');
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'WirecardSupport';
        $tab->name = array();
        $tab->name[1] = $key;
        foreach (Language::getLanguages(false) as $lang) {
            $translated_string = $this->getTranslationForLanguage(
                $lang['iso_code'],
                $key,
                $this->name
            );
            $tab->name[$lang['id_lang']] = $translated_string !== $key ?
                $translated_string : $tab->name[1];
        }
        $tab->module = $this->name;
        $tab->add();

        $key = $this->l('heading_title_ajax');
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'WirecardAjax';
        $tab->name = array();
        $tab->name[1] = $key;
        foreach (Language::getLanguages(false) as $lang) {
            $translated_string = $this->getTranslationForLanguage(
                $lang['iso_code'],
                $key,
                $this->name
            );
            $tab->name[$lang['id_lang']] = $translated_string !== $key ?
                $translated_string : $tab->name[1];
        }
        $tab->module = $this->name;
        $tab->id_parent = -1;
        $tab->add();
    }

    public function uninstallTabs()
    {
        $tabs = array('WirecardTransactions','WirecardSupport', 'WirecardAjax');
        foreach ($tabs as $tab) {
            $id_tab = (int)Tab::getIdFromClassName($tab);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
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
        /** @var Payment $payment */
        foreach ($this->getPayments() as $payment) {
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
            $this->html .= $this->displayError($this->l('warning_credit_card_url_mismatch'));
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
     * Get configuration parameters from config
     *
     * @return array
     * @since 1.0.0
     */
    public function getAllConfigurationParameters()
    {
        $params = array();
        foreach ($this->config as $group) {
            foreach ($group['fields'] as $f) {
                if ('hidden' == $f['type']) {
                    continue;
                }
                $f['param_name'] = $this->buildParamName(
                    $group['tab'],
                    $f['name']
                );
                $params[] = $f;
            }
        }

        return $params;
    }

    /**
     * Payment options hook
     *
     * @param $params
     * @return bool|void
     * @since 1.0.0
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $result = array();
        /** @var Payment $paymentMethod */
        foreach ($this->getPayments() as $paymentMethod) {
            if (! $this->getConfigValue($paymentMethod->getType(), 'enabled')) {
                continue;
            }

            if (! $paymentMethod->isAvailable($this, $params['cart'])) {
                continue;
            }

            $paymentData = array(
                'paymentType' => $paymentMethod->getType(),
            );
            if ('invoice' == $paymentMethod->getType()) {
                /** @var PaymentGuaranteedInvoiceRatepay $paymentMethod */
                $this->createRatepayScript($paymentMethod);
            }
            $payment = new PaymentOption();
            $payment
                ->setModuleName('wd-' . $paymentMethod->getType())
                ->setCallToActionText($this->l($this->getConfigValue($paymentMethod->getType(), 'title')))
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', $paymentData, true));

            if ($paymentMethod->getTemplateData()) {
                $this->context->smarty->assign($paymentMethod->getTemplateData());
            }

            if ($paymentMethod->getAdditionalInformationTemplate()) {
                $payment->setAdditionalInformation($this->fetch(
                    'module:' . $paymentMethod->getAdditionalInformationTemplate() . '.tpl'
                ));
            }

            $payment->setLogo(
                Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paymenttypes/'
                    . $paymentMethod->getType() . '.png')
            );
            $result[] = $payment;
        }

        //Implement action validation before payment
        return count($result) ? $result : false;
    }

    /**
     * Create ratepay script and device ident
     *
     * @param PaymentGuaranteedInvoiceRatepay $paymentMethod
     * @since 1.0.0
     */
    public function createRatepayScript($paymentMethod)
    {
        $merchantAccount = $this->getConfigValue('invoice', 'merchant_account_id');
        $deviceIdent = $paymentMethod->createDeviceIdent($merchantAccount);

        if (!isset($this->context->cookie->wirecardDeviceIdent)) {
            $this->context->cookie->wirecardDeviceIdent = $deviceIdent;
        }

        $this->context->smarty->assign(array('deviceIdent' => $this->context->cookie->wirecardDeviceIdent));

        try {
            echo $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'wirecardpaymentgateway' . DIRECTORY_SEPARATOR .
                'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR .
                'ratepayscript.tpl');
        } catch (SmartyException $e) {
        } catch (Exception $e) {
        }

        $paymentMethod->setAdditionalInformationTemplate(
            'invoice',
            array('deviceIdent' => $this->context->cookie->wirecardDeviceIdent)
        );
    }

    /**
     * Get payment class from payment type
     *
     * @param $paymentType
     * @return bool|Payment
     * @since 1.0.0
     */
    public function getPaymentFromType($paymentType)
    {
        $payments = $this->getPayments();

        if ('ratepay-invoice' == $paymentType) {
            $paymentType = 'invoice';
        }
        if (array_key_exists($paymentType, $payments)) {
            return $payments[$paymentType];
        }

        return false;
    }

    /**
     * Build prefix for configuration entries
     *
     * @param $name
     * @param $field
     *
     * @return string
     * @since 1.0.0
     */
    public function buildParamName($name, $field)
    {
        return sprintf(
            'WIRECARD_PAYMENT_GATEWAY_%s_%s',
            Tools::strtoupper($name),
            Tools::strtoupper($field)
        );
    }

    /**
     * Get Configuration value for specific field
     *
     * @param $name
     * @param $field
     * @return mixed
     * @since 1.0.0
     */
    public function getConfigValue($name, $field)
    {
        if ('sofortbanking' == $name) {
            $name = 'Sofort';
        }
        return Configuration::get($this->buildParamName($name, $field));
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
            'integration' => IS_CORE ? 'EE_Core' : 'EE',
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
     * Basic array of payment models
     *
     * @return array
     * @since 1.0.0
     */
    private function getPayments()
    {
        $payments = array(
            'creditcard' => new PaymentCreditCard($this),
            'paypal' => new PaymentPaypal($this),
            'sepadirectdebit' => new PaymentSepaDirectDebit($this),
            'sepacredittransfer' => new PaymentSepaCreditTransfer($this),
            'sofortbanking' => new PaymentSofort($this),
            'ideal' => new PaymentIdeal($this),
            'invoice' => new PaymentGuaranteedInvoiceRatepay($this),
            'p24' => new PaymentPtwentyfour($this),
            'poipia' => new PaymentPoiPia($this),
            'masterpass' => new PaymentMasterpass($this),
            'alipay-xborder' => new PaymentAlipayCrossborder($this)
        );

        return $payments;
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
        $this->html .= $this->displayConfirmation($this->l('settings_updated'));
    }

    /**
     * Check if the defined URLs for credit card payments are valid
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isUrlConfigurationValid()
    {
        $baseUrl = $this->getConfigValue('creditcard', 'base_url');
        $wppUrl = $this->getConfigValue('creditcard', 'wpp_url');

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
                'label' => $this->l('text_enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('text_disabled')
            )
        );

        $tempFields = $this->createInputFields($radioType, $radioOptions);
        $inputFields = $tempFields['inputFields'];
        $tabs = $tempFields['tabs'];

        $fields = array(
            'form' => array(
                'tabs' => $tabs,
                'legend' => array(
                    'title' => $this->l('payment_method_settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputFields,
                'submit' => array(
                    'title' => $this->l('text_save')
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
            foreach ($value['fields'] as $f) {
                if ('hidden' == $f['type']) {
                    continue;
                }
                $elem = array(
                    'name' => $this->buildParamName($tabname, $f['name']),
                    'label' => isset($f['label'])?$this->l($f['label']):'',
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
            foreach ($config['fields'] as $field) {
                if (array_key_exists('default', $field)) {
                    $name = $config['tab'];
                    $configParam = $this->buildParamName($name, $field['name']);
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
            foreach ($config['fields'] as $field) {
                $name = $config['tab'];
                $fieldname = $this->buildParamName($name, $field['name']);
                $value = Configuration::get($fieldname);
                if (isset($value)) {
                    if (!Configuration::deleteByName($fieldname)) {
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
                "masked_pan" => array( "VARCHAR(30)", "NOT NULL" )
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
        $link = new Link;
        $wppUrl = $this->getConfigValue('creditcard', 'wpp_url');

        $this->context->controller->registerJavascript(
            'wd-wpp',
            $wppUrl . '/loader/paymentPage.js',
            array('server' => 'remote', 'position' => 'top', 'priority' => 1)
        );

        foreach ($this->getPayments() as $paymentMethod) {
            if ($paymentMethod->getLoadJs()) {
                $ajaxLink = $link->getModuleLink('wirecardpaymentgateway', 'configprovider');
                $ccVaultLink = $link->getModuleLink('wirecardpaymentgateway', 'creditcard');
                $ajaxSepaUrl = $link->getModuleLink('wirecardpaymentgateway', 'sepadirectdebit');
                Media::addJsDef(
                    array(
                        'configProviderURL' => $ajaxLink,
                        'ccVaultURL' => $ccVaultLink,
                        'ajaxsepaurl' => $ajaxSepaUrl,
                        'cartId' => $this->context->cart->id,
                    )
                );

                $this->context->controller->addJS(
                    _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'views'
                    . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $paymentMethod->getType() . '.js'
                );
            }
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
            return $key;
        }

        global $_MODULE;
        include($file);
        $hashed_key = md5($key);
        $translation_key = '<{'.'wirecardpaymentgateway'.'}prestashop>'.\Tools::strtolower($file_name).'_'.$hashed_key;

        if (isset($_MODULE[$translation_key])) {
            return $_MODULE[$translation_key];
        } else {
            return $key;
        }
    }

    /**
     * Hook for registering new functions to smarty
     *
     * @since 1.3.4
     */
    public function hookActionDispatcher()
    {
        $this->context->smarty->registerPlugin('function', 'lFallback', array('WirecardPaymentGateway', 'lFallback'));
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

        return $translation;
    }

    /**
     * Overwritten translation function, uses the modules translation function with fallback language functionality
     *
     * @param string $key translation key
     * @param string|bool $specific filename of the translation key
     * @param string|null $locale not used!
     *
     * @return string translation
     * @since 1.3.4
     */
    public function l($key, $specific = false, $locale = null)
    {
        if (!$specific) {
            $specific = $this->name;
        }

        $translation = parent::l($key, $specific);
        if ($translation === $key) {
            $translation = WirecardPaymentGateway::getTranslationForLanguage('en', $key, $specific);
        }

        return $translation;
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
}
