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

    const PHRASEAPP_KEY_GENERIC_MESSAGE = 'error_message_generic';

    const PAYMENT_ERRORS_KEY_MAP = [
        self::PHRASEAPP_KEY_GENERIC_MESSAGE,
    ];

    /**
     * @var string
     *
     * @since 2.10.0
     */
    const TRANSLATION_FILE = 'paymenterrorhelper';

    /**
     * Return translated messages
     * @param string[] $errorMessageKeys
     * @return string[]
     *
     * @since 2.10.0
     */
    public function getTranslatedErrorMessages($errorMessageKeys)
    {
        $translatedMessages = [];
        foreach ($errorMessageKeys as $errorMessageKey) {
            $translatedMessages[] = $this->getTranslationForKey($errorMessageKey);
        }
        return $translatedMessages;
    }

    /**
     * If errorMessageKey is in errors key map, than translate, else return original value
     * @param string $errorMessageKey
     * @return string
     */
    private function getTranslationForKey($errorMessageKey)
    {
        $paymentErrorKeyMap = self::PAYMENT_ERRORS_KEY_MAP;
        if (in_array($errorMessageKey, $paymentErrorKeyMap)) {
            return $this->getTranslatedString($errorMessageKey, $this->getUserFrontendLanguage());
        }
        return $errorMessageKey;
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
            if ($id_lang === (int)$language["id_lang"]) {
                return $language['iso_code'];
            }
        }
        return 'en';
    }
}
