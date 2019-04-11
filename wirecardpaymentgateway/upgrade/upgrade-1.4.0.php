<?php

if (!defined('_PS_VERSION_')) {
exit;
}

function upgrade_module_1_3_5($object)
{
    return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'wirecard_payment_gateway_cc` ADD `last_used` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `masked_pan`');
}
