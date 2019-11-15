<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

const _PS_MODULE_DIR_ = 'modules';
const _PS_PRICE_COMPUTE_PRECISION_ = 2;
const _DB_PREFIX_ = 'Prefix_';
const _MYSQL_ENGINE_ = 'mysql';
const _PS_USE_SQL_SLAVE_ = 'slave';

const EXPECTED_PLUGIN_NAME = 'prestashop-ee+Wirecard';
const EXPECTED_SHOP_NAME = 'Prestashop';

require_once __DIR__ . '/../wirecardpaymentgateway/vendor/autoload.php';
require_once __DIR__ . '/util/functions.php';

//stub objects
require __DIR__ . '/Stubs/Currency.php';
require __DIR__ . '/Stubs/ObjectModel.php';
require __DIR__ . '/Stubs/Controller.php';
require __DIR__ . '/Stubs/ModuleFrontController.php';
require __DIR__ . '/Stubs/ModuleAdminController.php';
require __DIR__ . '/Stubs/Module.php';
require __DIR__ . '/Stubs/PaymentModule.php';
require __DIR__ . '/Stubs/Tools.php';
require __DIR__ . '/Stubs/Configuration.php';
require __DIR__ . '/Stubs/HelperForm.php';
require __DIR__ . '/Stubs/Language.php';
require __DIR__ . '/Stubs/Context.php';
require __DIR__ . '/Stubs/Link.php';
require __DIR__ . '/Stubs/Smarty.php';
require __DIR__ . '/Stubs/Media.php';
require __DIR__ . '/Stubs/PaymentOption.php';
require __DIR__ . '/Stubs/Cart.php';
require __DIR__ . '/Stubs/Product.php';
require __DIR__ . '/Stubs/Customer.php';
require __DIR__ . '/Stubs/Address.php';
require __DIR__ . '/Stubs/Country.php';
require __DIR__ . '/Stubs/State.php';
require __DIR__ . '/Stubs/PrestaShopLogger.php';
require __DIR__ . '/Stubs/Db.php';
require __DIR__ . '/Stubs/Tab.php';
require __DIR__ . '/Stubs/Order.php';
require __DIR__ . '/Stubs/OrderState.php';
require __DIR__ . '/Stubs/Validate.php';
require __DIR__ . '/Stubs/Cookie.php';
require __DIR__ . '/Stubs/Translate.php';
require __DIR__ . '/Stubs/DbQuery.php';

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';

function pSQL($string)
{
    return $string;
};
