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

    const PAYMENT_ERRORS_KEY_MAP = [
        'PHRASEAPP_KEY_GENERIC_MESSAGE' => 'error_message_generic',
    ];

    /**
     * @var string
     *
     * @since 2.10.0
     */
    const TRANSLATION_FILE = 'paymenterrorhelper';

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
            if ($this->isKeyInArray($errorMessage)) {
                $translatedMessages[] = $this->getTranslatedString($errorMessage, $this->getUserFrontendLanguage());
            } else {
                $translatedMessages[] = $errorMessage;
            }
        }
        return $translatedMessages;
    }

    private function isKeyInArray($translationKey)
    {
        $paymentErrorKeyMap = self::PAYMENT_ERRORS_KEY_MAP;
        foreach (array_values($paymentErrorKeyMap) as $paymentErrorKey) {
            if ($translationKey === $paymentErrorKey) {
                return true;
            }
        }
        return false;
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
