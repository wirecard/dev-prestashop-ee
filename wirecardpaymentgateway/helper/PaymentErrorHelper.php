<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use Language;

/**
 * Class PaymentErrorHelper
 * @package WirecardEE\Prestashop\Helper
 *
 * @since 2.10.0
 */
class PaymentErrorHelper
{
    use TranslationHelper;

    /**
     * @var string
     *
     * @since 2.10.0
     */
    const TRANSLATION_FILE = 'payment';

    /**
     * @param array $errorMessages
     * @return string[]
     *
     * @since 2.10.0
     */
    public function getTranslatedErrorMessages($errorMessages)
    {
        $translatedMessages = [];
        foreach ($errorMessages as $errorMessage) {
            $translatedMessages[] = $this->translateErrorMessage(
                $errorMessage,
                $this->getUserFrontendLanguage()
            );
        }
        return $translatedMessages;
    }

    /**
     * Translate error message key
     * @param string $errorMessageKey
     * @param string $lang_code
     * @return string
     *
     * @since 2.10.0
     */
    private function translateErrorMessage($errorMessageKey, $lang_code)
    {
        switch ($errorMessageKey) {
            case 'error_message_generic':
                return $this->getTranslatedString('error_message_generic', $lang_code);
            default:
                return $errorMessageKey;
        }
    }

    /**
     * Get frontend language from current user
     * @return string
     *
     * @since 2.10.0
     */
    private function getUserFrontendLanguage()
    {
        global $cookie;
        $id_lang = $cookie->id_lang;
        foreach (Language::getLanguages() as $language) {
            if ($id_lang === intval($language["id_lang"])) {
                return $language['iso_code'];
            }
        }
        return 'en';
    }
}
