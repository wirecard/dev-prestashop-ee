<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

/**
 * Interface ProcessableResponse
 * @package WirecardEE\Prestashop\Classes\Response
 */
interface ProcessablePaymentResponse
{
    /**
     * @since 2.1.0
     */
    public function process();
}
