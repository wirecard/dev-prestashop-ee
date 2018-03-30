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

/**
 * @property WirecardTransactionsController module
 */
class WirecardSupportController extends ModuleAdminController
{
    /** @var string */
    protected $display = 'add';

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('wirecardpaymentgateway');
        $this->bootstrap = true;
        $this->tpl_form_vars['back_url'] = $this->context->link->getAdminLink('AdminModules') . '&configure=' .
            $this->module->name . '&module_name=' . $this->module->name;
        parent::__construct();
    }

    /**
     * Set toolbar title
     *
     * @return void
     */
    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {
            case 'add':
                $this->toolbar_title[] = $this->l('Send support request');
                $this->addMetaTitle($this->l('Send support request'));
                break;
        }
    }

    /**
     * render form
     * @return string
     */
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Send support request'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('To:'),
                    'desc' => $this->l('Choose a support channel'),
                    'name' => 'to',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'support.at@wirecard.com',
                                'name' => 'Support Team Wirecard CEE, Austria'
                            ),
                            array('id_option' => 'support@wirecard.com', 'name' => 'Support Team Wirecard AG, Germany'),
                            array('id_option' => 'support.sg@wirecard.com', 'name' => 'Support Team Wirecard Singapore')
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'class' => 'fixed-width-xxl'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Your e-mail address:'),
                    'name' => 'replyto',
                    'required' => true,
                    'validation' => 'isEmail',
                    'class' => 'fixed-width-xxxl'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Your message:'),
                    'name' => 'message'
                ),
            ),
            'submit' => array(
                'name' => 'sendrequest',
                'title' => $this->l('Send'),
            )

        );

        return parent::renderForm();
    }

    /**
     * @see AdminController::initProcess()
     */
    public function initProcess()
    {
        parent::initProcess();

        $this->display = 'add';
        if (Tools::isSubmit('sendrequest')) {
            if (!Tools::getValue('replyto') || !Validate::isEmail(Tools::getValue('replyto'))) {
                $this->errors[] = Tools::displayError('Please enter a valid e-mail address');
            }

            if (!Tools::getValue('to') || !Validate::isEmail(Tools::getValue('to'))) {
                $this->errors[] = Tools::displayError('Please choise a valid support channel');
            }

            if (!Tools::getValue('message')) {
                $this->errors[] = Tools::displayError('Please enter your message');
            }

            if (!count($this->errors)) {
                $this->action = 'sendSupportRequest';
            }
        }
    }

    /**
     * send support request
     */
    public function processSendSupportRequest()
    {
        $modules = array();
        foreach (Module::getPaymentModules() as $m) {
            $modules[] = $m['name'];
        }

        $info = array(
            'prestaversion' => _PS_VERSION_,
            'pluginname' => $this->module->name,
            'pluginversion' => $this->module->version
        );

        $message = strip_tags(Tools::getValue('message'));

        $config = $this->module->getConfigFieldsValues();
        unset($config['WCS_BASICDATA_SECRET']);
        unset($config['WCS_BASICDATA_BACKENDPW']);

        $tmpl_vars = array(
            'message' => $message,
            'info' => print_r($info, true),
            'config' => print_r($config, true),
            'modules' => print_r($modules, true),
        );
        $lang = new Language;

        $res = Mail::Send(
            $lang->getIdByIso('en'),
            'support_contact',
            'Prestashop support request',
            $tmpl_vars,
            Tools::getValue('to'),
            null, // to_name
            null, // from
            null, // from_name
            null, // file_attachment,
            null, // mode_smtp
            _PS_MODULE_DIR_ . $this->module->name . '/mails/',
            false, // die
            null, // id_shop
            null, // bcc$
            Tools::getValue('replyto')
        );

        if ($res === false) {
            $this->errors[] = $this->l('There was an error during e-mail delivery');
        } else {
            $this->confirmations[] = $this->l('E-Mail sent successfully');
        }
    }
}
