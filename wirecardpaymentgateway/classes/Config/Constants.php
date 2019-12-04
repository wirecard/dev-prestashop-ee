<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config;

/**
 * Class Constants
 *
 * @package WirecardEE\Prestashop\Classes\Config
 * @since 2.3.1
 */
class Constants
{
    /** @var string  */
    const SETTING_GENERAL_AUTOMATIC_CAPTURE_ENABLED =
        'WIRECARD_PAYMENT_GATEWAY_GENERAL_AUTOMATIC_CAPTURE_ENABLED';
    /** @var string  */
    const SETTING_GENERAL_FORCE_ORDER_STATE_CHANGE_ENABLED =
        'WIRECARD_PAYMENT_GATEWAY_GENERAL_FORCE_ORDER_STATE_CHANGE_ENABLED';
}
