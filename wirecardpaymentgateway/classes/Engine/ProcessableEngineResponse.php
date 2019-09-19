<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Engine;

/**
 * Interface ProcessableEngineResponse
 *
 * @package WirecardEE\Prestashop\Classes\Engine
 * @since 2.1.0
 */
interface ProcessableEngineResponse
{
    /**
     * @param array|string $response
     * @since 2.1.0
     */
    public function process($response);
}
