<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_0($object)
{
    $sql = 'ALTER TABLE `'._DB_PREFIX_.
        'wirecard_payment_gateway_cc` ADD `last_used` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `masked_pan`;';
    $sql .='UPDATE `'._DB_PREFIX_.'configuration` SET `value` = "purchase" WHERE'.
        ' `name` = "WIRECARD_PAYMENT_GATEWAY_CREDITCARD_PAYMENT_ACTION" AND `value`="pay";';
    $sql .='UPDATE `'._DB_PREFIX_.'configuration` SET `value` = "authorization" WHERE'.
        ' `name` = "WIRECARD_PAYMENT_GATEWAY_CREDITCARD_PAYMENT_ACTION" AND `value`="reserve";';
    $sql .='UPDATE `'._DB_PREFIX_.'configuration` SET `value` = "purchase" WHERE'.
        ' `name` = "WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_PAYMENT_ACTION" AND `value`="pay";';
    $sql .='UPDATE `'._DB_PREFIX_.'configuration` SET `value` = "authorization" WHERE'.
        ' `name` = "WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_PAYMENT_ACTION" AND `value`="reserve";';
    return Db::getInstance()->execute($sql);
}
