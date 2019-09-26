<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Language
{
    public $id = 'test';
    public $iso_code = 'en';
    public $language_code = 'en-us';

    public static function getLanguages()
    {
        return array(array('id_lang' => 'de', 'iso_code' => 'de', 'language_code' => 'de-de'), array('id_lang' => 'en', 'iso_code' => 'en', 'language_code' => 'en-en'));
    }
}
