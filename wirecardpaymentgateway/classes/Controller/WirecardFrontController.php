<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Controller;

/**
 * Class WirecardFrontController
 * @package WirecardEE\Prestashop\Classes\Controller
 * @since 2.2.2
 */
class WirecardFrontController extends \ModuleFrontController
{
    /**
     * Returns null, since we use our own rendering path
     *
     * @return null
     * @since 2.2.2
     */
    public function display()
    {
        return null;
    }
}
