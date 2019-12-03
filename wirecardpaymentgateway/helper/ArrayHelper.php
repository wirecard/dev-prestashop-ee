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
class ArrayHelper
{
    /**
     * Filters array trough items starting with specified prefix
     * @param array $data
     * @param string $prefix
     * @return array
     * @since 2.5.0
     */
    public static function filterWithPrefix($data, $prefix)
    {
        $filteredData = [];

        foreach ($data as $paramName => $value) {
            if (strpos($paramName, $prefix) !== false) {
                $filteredData[$paramName] = $value;
            }
        }

        return $filteredData;
    }
}
