<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Link
{
    public function getAdminLink($string)
    {
        return $string;
    }

    public function getModuleLink($name = null, $func = null, $array = null)
    {
        return 'http://test.com';
    }

    public function getPageLink($link)
    {
        return $link;
    }
}
