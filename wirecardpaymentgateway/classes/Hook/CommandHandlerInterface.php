<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Hook;

/**
 * Interface CommandHandlerInterface
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Hook
 */
interface CommandHandlerInterface
{
    /**
     * Handle command
     * @since 2.5.0
     */
    public function handle();
}
