<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */


use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Classes\Config\Constants;
use WirecardEE\Prestashop\Helper\Form\FormHelper;
use WirecardEE\Prestashop\Helper\Service\GeneralSettingsService;

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

    const FORM_SUBMIT_ID = "send_request";

    const DISPLAY_VIEW_NAME_ADD = "add";

    /**
     * WirecardGeneralSettingsController constructor.
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->context = Context::getContext();
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
     */
    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {
            case self::DISPLAY_VIEW_NAME_ADD:
                $this->toolbar_title[] = $this->getTranslatedString('general_settings_title');// @TODO: Translation
                $this->addMetaTitle($this->getTranslatedString('general_settings_title'));// @TODO: Translation
                break;
        }
    }

    /**
     * render form
     * @return string
     * @throws SmartyException
     * @throws Exception
     */
    public function renderForm()
    {
        $formHelper = new FormHelper();
        $formHelper->addSwitchInput(
            Constants::SETTING_GENERAL_AUTOMATIC_CAPTURE_ENABLED,
            $this->getTranslatedString('text_automatic_capture_enabled'), // @TODO: Translation
            [],  // default values
            ['desc' => $this->getTranslatedString('text_automatic_capture_description')]  // @TODO: Translation
        );
        $formHelper->addSwitchInput(
            Constants::SETTING_GENERAL_FORCE_ORDER_STATE_CHANGE_ENABLED,
            $this->getTranslatedString('text_force_order_state_change_enabled'), // @TODO: Translation
            [],  // default values
            ['desc' => $this->getTranslatedString('text_force_order_state_change_description')]  // @TODO: Translation
        );

        // ------ Add elements to form ------

        // Add submit button
        $formHelper->addSubmitButton(
            self::FORM_SUBMIT_ID,
            $this->getTranslatedString('save_general_settings')
        ); // @TODO: Translation

        // Add input fields
        $this->fields_form = $formHelper->buildForm();

        // Add actual values of input fields
        $this->fields_value = $formHelper->getFormValues();

        return parent::renderForm();
    }

    /**
     * @throws PrestaShopException
     * @see AdminController::initProcess()
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
                $this->confirmations[] = $this->getTranslatedString('settings_updated');// @TODO: Translation
            }
        }
    }
}
