<?php

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
