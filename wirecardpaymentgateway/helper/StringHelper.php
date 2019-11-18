<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

/**
 * Class StringHelper
 * @package WirecardEE\Prestashop\Helper
 * @since 2.5.0
 */
class StringHelper {

    /**
     * string with specified prefix
     * @param string $value
     * @param string $prefix
     * @return string
     * @since 2.5.0
     */
    public static function beginFrom($value, $prefix)
    {
        $newString = substr($value, strpos($value, $prefix) + strlen($prefix));
        return strval($newString);
    }

    /**
     * @param string $value
     * @param array $searchList
     * @param array $replacementList
     * @return string
     * @since 2.5.0
     */
    public static function replaceWith($value, $searchList = ['-'], $replacementList = ['_'])
    {
        return strval(str_replace($searchList, $replacementList, $value));
    }
}
