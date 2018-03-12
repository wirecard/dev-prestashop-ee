<?php
const _PS_MODULE_DIR_ = './';

require_once __DIR__ . '/../wirecardpaymentgateway/vendor/autoload.php';

//stub objects
require __DIR__ . '/Stubs/Currency.php';
require __DIR__ . '/Stubs/Controller.php';
require __DIR__ . '/Stubs/ModuleFrontController.php';
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

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';
