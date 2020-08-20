<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

use WirecardEE\Prestashop\Classes\Constants\ConfigConstants;
use WirecardEE\Prestashop\Helper\Form\FormHelper;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\GeneralSettingsService;
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Class WirecardGeneralSettingsController
 * @since 2.5.0
 * @property WirecardPaymentGateway $module
 */
class WirecardGeneralSettingsController extends ModuleAdminController
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardgeneralsettings";

    /** @var string */
    const FORM_SUBMIT_ID = "send_request";

    /** @var string */
    const DISPLAY_VIEW_NAME_ADD = "add";

    /** @var ContextService */
    protected $context_service;

    /**
     * WirecardGeneralSettingsController constructor.
     * @throws PrestaShopException
     * @since 2.5.0
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->context_service = new ContextService(Context::getContext());
        $this->module = Module::getInstanceByName('wirecardpaymentgateway');
        $this->bootstrap = true;
        $this->tpl_form_vars['back_url'] = $this->context->link->getAdminLink('AdminModules') . '&configure=' .
            $this->module->name . '&module_name=' . $this->module->name;

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $this->tpl_form_vars['default_form_language'] = $lang->id;

        parent::__construct();
    }

    /**
     * Set toolbar title
     *
     * @return void
     * @since 2.5.0
     */
    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {
            case self::DISPLAY_VIEW_NAME_ADD:
                $this->toolbar_title[] = $this->getTranslatedString('heading_title_general_settings');
                $this->addMetaTitle($this->getTranslatedString('heading_title_general_settings'));
                break;
        }
    }

    /**
     * render form
     * @return string
     * @throws \SmartyException
     * @throws Exception
     * @since 2.5.0
     */
    public function renderForm()
    {
        $formHelper = new FormHelper();
        $formHelper->addSwitchInput(
            ConfigConstants::SETTING_GENERAL_AUTOMATIC_CAPTURE_ENABLED,
            $this->getTranslatedString('text_automatic_capture_enabled'),
            [],  // default values
            ['desc' => $this->getTranslatedString('text_automatic_capture_description')]
        );

        $formHelper->addSubmitButton(
            self::FORM_SUBMIT_ID,
            $this->getTranslatedString('save_general_settings')
        );

        $this->fields_form = $formHelper->buildForm();

        $this->show_form_cancel_button = false;

        $this->fields_form['buttons'] = array(
            array(
                'href' => $this->tpl_form_vars['back_url'],
                'title' => $this->trans('Cancel', array(), 'Admin.Actions'),
                'icon' => 'process-icon-cancel'
            )
        );

        $this->fields_value = $formHelper->getFormValues();

        return parent::renderForm();
    }

    /**
     * @throws PrestaShopException
     * @see AdminController::initProcess()
     * @since 2.5.0
     */
    public function initProcess()
    {
        parent::initProcess();

        $this->display = self::DISPLAY_VIEW_NAME_ADD;

        if (Tools::isSubmit(self::FORM_SUBMIT_ID)) {
            $input = Tools::getAllValues();
            $generalSettingsService = new GeneralSettingsService();
            $result = $generalSettingsService->saveGeneralSettingsFromInput($input);
            $this->errors = array_merge($this->errors, $generalSettingsService->getErrors());

            if ($result && !count($this->errors)) {
                $this->confirmations[] = $this->getTranslatedString('settings_updated');
            }
        }
    }
}
