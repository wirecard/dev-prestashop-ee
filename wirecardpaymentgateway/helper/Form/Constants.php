<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Form;

/**
 * Class Constants
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Helper\Form
 */
class Constants
{
    const FORM_GROUP_TYPE_INPUT = "input";
    const FORM_GROUP_TYPE_SUBMIT = "submit";

    const FORM_ELEMENT_TYPE_SWITCH = "switch";
    const FORM_ELEMENT_TYPE_SUBMIT = "submit";

    /**
     * @return array
     */
    public static function getGroupTypesWithChildren()
    {
        return [
            self::FORM_GROUP_TYPE_INPUT
        ];
    }

    /**
     * @return array
     */
    public static function getElementTypesWithValues()
    {
        return [
            self::FORM_ELEMENT_TYPE_SWITCH
        ];
    }
}
