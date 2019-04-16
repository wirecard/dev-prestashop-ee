<?php

namespace WirecardEE\Prestashop\Helper;

class SupportedHppLangCode
{

    /**
     * Get supported language code for hpp seamless form renderer
     *
     * @param string   $baseUrl
     * @param \Context $context
     * @return mixed|string
     */
    public static function getSupportedHppLangCode($baseUrl, $context)
    {
        $isoCode = $context->language->iso_code;
        $languageCode = $context->language->language_code;
        $language = 'en';
        //special case for chinese languages
        switch ($languageCode) {
            case 'zh-tw':
                $isoCode = 'zh_TW';
                break;
            case 'zh-cn':
                $isoCode = 'zh_CN';
                break;
            default:
                break;
        }
        try {
            $supportedLang = json_decode(\Tools::file_get_contents(
                $baseUrl . '/engine/includes/i18n/languages/hpplanguages.json'
            ));
            if (key_exists($isoCode, $supportedLang)) {
                $language = $isoCode;
            }
        } catch (\Exception $exception) {
            return 'en';
        }
        return $language;
    }
}
