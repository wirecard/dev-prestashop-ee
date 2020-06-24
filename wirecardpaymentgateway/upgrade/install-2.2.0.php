<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
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
function upgrade_module_2_2_0($module)
{
    // Set new parameter requestor_challenge
    $configService = new ShopConfigurationService('creditcard');
    $requestorChallenge = $configService->getFieldName('requestor_challenge');
    Configuration::updateGlobalValue($requestorChallenge, ChallengeInd::NO_PREFERENCE);

    // add new fields date_add and date_last_used in wirecard_payment_gateway_cc
    $table = '`' . _DB_PREFIX_ . 'wirecard_payment_gateway_cc`';
    $return = $module->executeSql("ALTER TABLE $table ADD `date_add` DATETIME NULL AFTER `masked_pan`");
    $return &= $module->executeSql("ALTER TABLE $table ADD `date_last_used` DATETIME NULL AFTER `date_add`");
    return $return;
}
