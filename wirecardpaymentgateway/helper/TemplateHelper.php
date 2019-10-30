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
 * Class TemplateHelper
 *
 * @package WirecardEE\Prestashop\Helper
 * @since 2.4.0
 */
class TemplateHelper
{
    /**
     * Gets the path for a template
     *
     * @param string $template
     * @return string
     * @since 2.4.0
     */
    public static function getTemplatePath($template)
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [_PS_MODULE_DIR_, \WirecardPaymentGateway::NAME, 'views', 'templates', 'front', $template . '.tpl']
        );
    }
}
