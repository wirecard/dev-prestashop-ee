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

require_once __DIR__ . '/vendor/autoload.php';

include_once(_PS_MODULE_DIR_ . 'wirecardpaymentgateway' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Payments' . DIRECTORY_SEPARATOR . 'PaymentPaypal.php');

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class WirecardPaymentGateway
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
     * WirecardPaymentGateway constructor.
     */
    public function __construct()
    {
        $this->name = 'wirecardpaymentgateway';
        $this->tab = 'payments_gateways';
        $this->version = '0.0.1';
        $this->author = 'Wirecard';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => '1.7.2.4');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Wirecard Payment Processing Gateway');
        $this->description = $this->l('Wirecard Payment Processing Gateway Plugin for Prestashop.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->config = $this->getPaymentFields();
    }

    /**
     * @return bool
     * @since 1.0.0
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @since 1.0.0
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     * @since 1.0.0
     */
    public function getPayments()
    {
        $payments = array(
            'paypal' => new PaymentPaypal()
        );

        return $payments;
    }

    /**
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
     * @return null|string
     * @since 1.0.0
     */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postProcess();
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->displayWirecardPaymentGateway();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    /**
     * @since 1.0.0
     */
    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);
                Configuration::updateValue($parameter['param_name'], $val);
            }
        }
        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    /**
     * @return string
     * @since 1.0.0
     */
    protected function displayWirecardPaymentGateway()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    /**
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
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter['param_name'] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter['param_name']] = $val;
            }
        }

        return $values;
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
    protected function buildParamName($name, $field)
    {
        return sprintf(
            'WIRECARD_PAYMENT_GATEWAY_%s_%s',
            Tools::strtoupper($name),
            Tools::strtoupper($field)
        );
    }

    /**
     * @return array
     * @since 1.0.0
     */
    public function getAllConfigurationParameters()
    {
        $params = array();
        foreach ($this->config as $group) {
            foreach ($group['fields'] as $f) {
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
     * @return mixed
     * @since 1.0.0
     */
    private function renderForm()
    {
        $radio_type = 'switch';

        $radio_options = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Disabled')
            )
        );

        $input_fields = array();
        $tabs = array();
        foreach ($this->config as $value) {
            $tabname = $value['tab'];
            $tabs[$tabname] = $tabname;
            foreach ($value['fields'] as $f) {
                $elem = array(
                    'name' => $this->buildParamName($tabname, $f['name']),
                    'label' => $this->l($f['label']),
                    'tab' => $tabname,
                    'type' => $f['type'],
                    'required' => isset($f['required']) && $f['required']
                );

                switch ($f['type']) {
                    case 'text':
                        if (!isset($elem['class'])) {
                            $elem['class'] = 'fixed-width-xl';
                        }

                        if (isset($f['maxchar'])) {
                            $elem['maxlength'] = $elem['maxchar'] = $f['maxchar'];
                        }
                        break;

                    case 'onoff':
                        $elem['type'] = $radio_type;
                        $elem['class'] = 't';
                        $elem['is_bool'] = true;
                        $elem['values'] = $radio_options;
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
                            }

                            if (method_exists($this, $optfunc)) {
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

        $fields_form_settings = array(
            'form' => array(
                'tabs' => $tabs,
                'legend' => array(
                    'title' => $this->l('Payment method settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $input_fields,
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );


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
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'ajax_configtest_url' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name
                . '&tab_module=' . $this->tab . '&module_name=' . $this->name
        );

        return $helper->generateForm(array($fields_form_settings));
    }
}
