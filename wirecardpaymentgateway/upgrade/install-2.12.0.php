<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade script v2.12.0
 * Remove token index to be able to save one token on multiple addresses
 *
 * @param WirecardPaymentGateway $module
 * @return bool
 * @since 2.12.0
 */
function upgrade_module_2_12_0($module)
{
    $table = '`' . _DB_PREFIX_ . 'wirecard_payment_gateway_cc`';
    $return = $module->executeSql("DROP INDEX token ON $table", 1061);
    $return &= $module->executeSql("ALTER TABLE $table ADD `address_hash` VARCHAR(32) AFTER `date_last_used`");

    return $return;
}
