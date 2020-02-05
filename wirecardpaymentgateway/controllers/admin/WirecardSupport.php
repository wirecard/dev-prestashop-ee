<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

/**
 * @property WirecardPaymentGateway module
 */
class WirecardSupportController extends ModuleAdminController
{
    use \WirecardEE\Prestashop\Helper\TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardsupport";

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
                $this->toolbar_title[] = $this->getTranslatedString('support_email_title');
                $this->addMetaTitle($this->getTranslatedString('support_email_title'));
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
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslatedString('config_email'),
                    'name' => 'replyto',
                    'required' => true,
                    'validation' => 'isEmail',
                    'class' => 'fixed-width-xxxl'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->getTranslatedString('config_message'),
                    'name' => 'message'
                ),
            ),
            'submit' => array(
                'name' => 'sendrequest',
                'title' => $this->getTranslatedString('send_email'),
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
                $this->errors[] = Tools::displayError($this->getTranslatedString('enter_valid_email_error'));
            }

            if (!Tools::getValue('message')) {
                $this->errors[] = Tools::displayError($this->getTranslatedString('enter_email_message_error'));
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

        $config = $this->module->getNonConfidentialConfigFieldsValues();

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
            'shop-systems-support@wirecard.com',
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
            $this->errors[] = $this->getTranslatedString('error_email');
        } else {
            $this->confirmations[] = $this->getTranslatedString('success_email');
        }
    }
}
