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

namespace WirecardEE\Prestashop\Helper\Service;

use \Configuration;
use \Tools;
use WirecardEE\Prestashop\Helper\ArrayHelper;
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Class GeneralSettingsService
 *
 * @since 2.5.0
 * @package WirzecardEE\Prestashop\Helper\Service
 */
class GeneralSettingsService
{
    use TranslationHelper;

    /** @var string  */
    const WIRECARD_SETTING_PREFIX = "WIRECARD_PAYMENT_GATEWAY";

    /** @var string */
    const TRANSLATION_FILE = 'wirecardajax';

    /**
     * @var array
     */
    private $validationErrors = [];

    /**
     * @return array
     * @since 2.5.0
     */
    public function getErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @param $error
     * @since 2.5.0
     */
    public function addValidationError($error)
    {
        $this->validationErrors[] = $error;
    }

    /**
     * Validate input
     * @param $settings
     * @return bool
     * @throws \PrestaShopException
     * @since 2.5.0
     */
    public function validateInput($settings)
    {
        $result = true;
        if (empty($settings)) {
            $result = false;
            $this->addValidationError(Tools::displayError($this->getTranslatedString('settings_is_empty')));
        }

        return $result;
    }

    /**
     * Create or save settings from input
     * @param array $settings
     * @return bool
     * @throws \PrestaShopException
     * @since 2.5.0
     */
    public function saveGeneralSettingsFromInput(array $settings)
    {
        $result = $this->validateInput($settings);
        if ($result) {
            $wirecardSettings = ArrayHelper::filterWithPrefix($settings, self::WIRECARD_SETTING_PREFIX);
            foreach ($wirecardSettings as $setting => $value) {
                $updateResult = Configuration::updateValue($setting, $value);
                if (!$updateResult) {
                    $this->addValidationError("Setting {$setting} wasn't saved!");
                    $result = false;
                }
            }
        }

        return $result;
    }
}
