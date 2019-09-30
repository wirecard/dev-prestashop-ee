<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Country
{
    public $iso_code;

    public static function getCountries($var = null)
    {
        return array(
            array( 'iso_code' => 'AT', 'name' => 'Austria')
        );
    }

    public function __construct($countryId)
    {
    }
}
