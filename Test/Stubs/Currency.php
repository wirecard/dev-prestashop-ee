<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Currency
{
    public $id;
    public $name;
    public $iso_code = 'EUR';

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        $this->id = 1;
        $this->name = 'Euro';
        $this->iso_code = 'EUR';
        $this->conversion_rate = '1';
    }

    public static function getCurrencies()
    {
        return array(
            array('iso_code' => 'EUR', 'name' => 'Euro', 'conversion_rate' => '1'),
            array('iso_code' => 'USD', 'name' => 'US Dollar', 'conversion_rate' => '0.5'),
        );
    }

    public static function getTestCurrency()
    {
        return array('EUR');
    }
}
