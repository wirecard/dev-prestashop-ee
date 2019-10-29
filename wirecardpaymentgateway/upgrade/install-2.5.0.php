<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

$moduleName = 'wirecardpaymentgateway';
$moduleDirectory = _PS_MODULE_DIR_ . $moduleName;
define('DS', DIRECTORY_SEPARATOR);

require_once $moduleDirectory . DS . 'vendor' . DS . 'wirecard' . DS . 'payment-sdk-php' .
    DS . 'src' . DS . 'Constant/ChallengeInd.php';
require_once $moduleDirectory . DS . 'helper' . DS . 'service' . DS . 'ShopConfigurationService.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade script v2.5.0
 * @param WirecardPaymentGateway $module
 * @return bool
 * @since 2.5.0
 */
function upgrade_module_2_5_0($module)
{
    $module->registerHook('postUpdateOrderStatus');
    $module->registerHook('updateOrderStatus');
    // Set new settings
    // Configuration::updateValue();

    return true;
}
