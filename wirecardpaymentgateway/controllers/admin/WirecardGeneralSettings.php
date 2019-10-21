<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */


use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use \WirecardEE\Prestashop\Helper\TranslationHelper;
use \WirecardEE\Prestashop\Classes\Config\Constants;

/**
 * @property WirecardPaymentGateway $module
 */
class WirecardGeneralSettingsController extends ModuleAdminController
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardgeneralsettings";

    const FORM_SUBMIT_ID = "send_request";

    const DISPLAY_VIEW_NAME_ADD = "add";

    const SETTING_ENABLED = "enabled";

    const WIRECARD_SETTING_PREFIX = "WIRECARD_PAYMENT_GATEWAY";

    /** @var array | WirecardEE\Prestashop\Models\Payment[] */
    private $paymentMethodConfig;

    /**
     * Get all payment methods
     * @return array|\WirecardEE\Prestashop\Models\Payment[]
     */
    public function getPaymentMethods()
    {
        if (null === $this->paymentMethodConfig) {
            $this->paymentMethodConfig = PaymentProvider::getPayments();
        }

        return $this->paymentMethodConfig;
    }

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
                $this->toolbar_title[] = $this->getTranslatedString('general_settings_title');
                $this->addMetaTitle($this->getTranslatedString('general_settings_title'));
                break;
        }
    }

    /**
     * @param string $fieldName
     * @param string $label
     * @return array
     */
    private function getSwitchInputItem($fieldName, $label)
    {
        $fieldId = $fieldName . "_enabled";
        return [
            'id'        => $fieldId,
            'name'      =>$fieldName,
            'label'     => $label,
            'type'      => 'switch',
            'required'  => false,
            'class'     => 't',
            'is_bool'   => true,
            'values'    => [
                [ 'id' => "active_on_{$fieldId}",  'value' => 1, 'label' => $this->getTranslatedString('text_enabled')],
                [ 'id' => "active_off_{$fieldId}", 'value' => 0, 'label' => $this->getTranslatedString('text_disabled')],
            ]
        ];
    }

    /**
     * render form
     * @return string
     * @throws SmartyException
     */
    public function renderForm()
    {
        $inputFields = [];
        $inputFieldCurrentValues = [];
        $paymentMethodConfig = $this->getPaymentMethods();
        // Auto capture turn on / off
        $inputFields[] = $this->getSwitchInputItem(Constants::CONFIGURATION_GENERAL_AUTOMATIC_CAPTURE_ENABLED, $this->getTranslatedString('text_automatic_capture_enabled'));
        $inputFieldCurrentValues[Constants::CONFIGURATION_GENERAL_AUTOMATIC_CAPTURE_ENABLED] = Configuration::get(Constants::CONFIGURATION_GENERAL_AUTOMATIC_CAPTURE_ENABLED);
        // Change order state anyway
        $inputFields[] = $this->getSwitchInputItem(Constants::CONFIGURATION_GENERAL_FORCE_ORDER_STATE_CHANGE_ENABLED, $this->getTranslatedString('text_force_order_state_change_enabled'));
        $inputFieldCurrentValues[Constants::CONFIGURATION_GENERAL_FORCE_ORDER_STATE_CHANGE_ENABLED] = Configuration::get(Constants::CONFIGURATION_GENERAL_FORCE_ORDER_STATE_CHANGE_ENABLED);
        // All payments enabling / disabling in one page
        foreach ($paymentMethodConfig as $paymentMethodType => $paymentMethod) {
            $shopConfigService = new ShopConfigurationService($paymentMethod->getType());
            $fieldName = $shopConfigService->getFieldName(self::SETTING_ENABLED);
            $inputFieldCurrentValues[$fieldName] = $shopConfigService->getField(self::SETTING_ENABLED);
            $inputFields[] = $this->getSwitchInputItem($fieldName, $paymentMethod->getName());
        }

        // ------ Add elements to form ------

        // Add input fields
        $this->fields_form['input'] = $inputFields;

        // Add submit button
        $this->fields_form['submit'] = [
            'name' => self::FORM_SUBMIT_ID,
            'title' => $this->getTranslatedString('save_general_settings'),
        ];

        // Add actual values of input fields
        $this->fields_value = $inputFieldCurrentValues;

        return parent::renderForm();
    }

    /**
     * Filters array trough items starting with specified prefix
     * @param array $data
     * @param string $prefix
     * @return array
     */
    public function findParamsStartingWithPrefix($data, $prefix = self::WIRECARD_SETTING_PREFIX)
    {
        $filteredData = [];

        foreach ($data as $paramName => $value) {
            if (strpos($paramName, $prefix) !== false) {
                $filteredData[$paramName] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * @see AdminController::initProcess()
     */
    public function initProcess()
    {
        parent::initProcess();

        $this->display = self::DISPLAY_VIEW_NAME_ADD;

        if (Tools::isSubmit(self::FORM_SUBMIT_ID)) {
            $values = Tools::getAllValues();
            $wirecardSettings = $this->findParamsStartingWithPrefix($values);

            foreach ($wirecardSettings as $setting => $value) {
                Configuration::updateValue($setting, $value);
            }

            if (!count($this->errors)) {
                $this->confirmations[] = $this->getTranslatedString('settings_updated');
            }
        }
    }
}
