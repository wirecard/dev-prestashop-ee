<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

class StringHelper {

    /**
     * Returns new string from defined "from" value
     * @param string $value
     * @param string $fromString
     * @return string
     * @since 2.5.0
     */
    public static function startFrom($value, $fromString)
    {
        $newString = substr($value, strpos($value, $fromString) + strlen($fromString));
        return strval($newString);
    }

    /**
     * Returns new string with replacement of specified values
     * @param string $value
     * @param array|string $search
     * @param array|string $replacement
     * @return string
     * @since 2.5.0
     */
    public static function replaceWith($value, $search, $replacement)
    {
        return strval(str_replace($search, $replacement, $value));
    }
}