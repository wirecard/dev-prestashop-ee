<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

require_once(_PS_MODULE_DIR_.'wirecardpaymentgateway'.DIRECTORY_SEPARATOR.'vendor'.
    DIRECTORY_SEPARATOR.'wirecard'.DIRECTORY_SEPARATOR.'payment-sdk-php'.
    DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Constant/ChallengeInd.php');

require_once(_PS_MODULE_DIR_.'wirecardpaymentgateway'.DIRECTORY_SEPARATOR.'helper'
    .DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'ShopConfigurationService.php');

use Wirecard\PaymentSdk\Constant\ChallengeInd;

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
    $configService = new \WirecardEE\Prestashop\Helper\Service\ShopConfigurationService('creditcard');
    $requestorChallenge = $configService->getFieldName('requestor_challenge');
    Configuration::updateGlobalValue($requestorChallenge, ChallengeInd::NO_PREFERENCE);

    // add new fields date_add and date_last_used in wirecard_payment_gateway_cc
    $table = '`' . _DB_PREFIX_ . 'wirecard_payment_gateway_cc`';
    $return = $module->executeSql("ALTER TABLE $table ADD `date_add` DATETIME NULL AFTER `masked_pan`");
    $return &= $module->executeSql("ALTER TABLE $table ADD `date_last_used` DATETIME NULL AFTER `date_add`");
    return $return;
}
