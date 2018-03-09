<?php
require_once __DIR__ . '/../wirecardpaymentgateway/vendor/autoload.php';

//stub objects
require __DIR__ . '/Stubs/ModuleFrontController.php';
require __DIR__ . '/Stubs/PaymentModule.php';
require __DIR__ . '/Stubs/Tools.php';
require __DIR__ . '/Stubs/Configuration.php';
require __DIR__ . '/Stubs/HelperForm.php';
require __DIR__ . '/Stubs/Language.php';
require __DIR__ . '/Stubs/Context.php';
require __DIR__ . '/Stubs/Link.php';
require __DIR__ . '/Stubs/Smarty.php';
require __DIR__ . '/Stubs/PaymentOption.php';
require __DIR__ . '/Stubs/Cart.php';
require __DIR__ . '/Stubs/Customer.php';
require __DIR__ . '/Stubs/Address.php';
require __DIR__ . '/Stubs/Country.php';

const _PS_MODULE_DIR_ = '';
$_SERVER['REMOTE_ADDR'] = 'Test';
