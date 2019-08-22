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

const _PS_MODULE_DIR_ = './';
const _PS_PRICE_COMPUTE_PRECISION_ = 2;
const _DB_PREFIX_ = 'Prefix_';
const _MYSQL_ENGINE_ = 'mysql';

const EXPECTED_PLUGIN_VERSION = '9.9.9';
const EXPECTED_PLUGIN_NAME = 'prestashop-ee+Wirecard';
const EXPECTED_SHOP_NAME = 'Prestashop';

require_once __DIR__ . '/../wirecardpaymentgateway/vendor/autoload.php';

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
