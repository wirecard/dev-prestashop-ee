<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

require_once(_PS_MODULE_DIR_.'wirecardpaymentgateway'.DIRECTORY_SEPARATOR.'vendor'.
    DIRECTORY_SEPARATOR.'wirecard'.DIRECTORY_SEPARATOR.'payment-sdk-php'.
    DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Constant/ChallengeInd.php');

require_once(_PS_MODULE_DIR_.'wirecardpaymentgateway'.DIRECTORY_SEPARATOR.'helper'
    .DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'ShopConfigurationService.php');

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade script v2.2.0
 * @param WirecardPaymentGateway $module
 * @return bool
 * @since 2.2.0
 */
function upgrade_module_2_3_1($module)
{
    // Set new settings
    // Configuration::updateValue();

    return true;
}
