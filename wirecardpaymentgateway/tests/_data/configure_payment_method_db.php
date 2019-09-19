<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

// the path for different config files, each named as <paymentmethod>.json
define('GATEWAY_CONFIG_PATH', 'gateway_configs');

$gateway = getenv('GATEWAY');
if (!$gateway) {
    $gateway = 'API-TEST';
}

// the default config defines valid keys for each payment method and is prefilled with API-TEST setup by default
$defaultConfig = [
    'creditcard' => [
        'base_url' => 'https://api-test.wirecard.com',
        'wpp_url' => 'https://wpp-test.wirecard.com',
        'http_user' => '70000-APITEST-AP',
        'http_pass' => 'qD2wzQ_hrc!8',
        'three_d_merchant_account_id' => '508b8896-b37d-4614-845c-26bf8bf2c948',
        'three_d_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
        'merchant_account_id' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
        'secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
        'ssl_max_limit' => 100,
        'three_d_min_limit' => 50,

        'enabled' => '1',
        'title' => 'Wirecard Credit Card',
        'credentials' => '',
        'test_button' => 'Test',
        'advanced' => '',
        'payment_action' => 'pay',
        'descriptor' => '0',
        'send_additional' => '1',
        'cc_vault_enabled' => '0',
    ],
    'paypal' => [
        'base_url' => 'https://api-test.wirecard.com',
        'http_user' => '70000-APITEST-AP',
        'http_pass' => 'qD2wzQ_hrc!8',
        'merchant_account_id' => '2a0e9351-24ed-4110-9a1b-fd0fee6bec26',
        'secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',

        'enabled' => '1',
        'title' => 'Wirecard PayPal',
        'descriptor' => '0',
        'payment_action' => 'pay',
        'send_additional' => '0',
    ]
];

$supportedPaymentActionsPerPaymentMethod = [
    'creditcard' => ['reserve', 'pay'],
    'paypal'     => ['reserve', 'pay']
];

// main script - read payment method from command line, build the config and write it into database
if (count($argv) < 3) {
    $supportedPaymentMethods = implode("\n  ", array_keys( $defaultConfig));
    $supportedPaymentActions = '';
    foreach ($defaultConfig as $key => $value) {
        $supportedPaymentActions .= $supportedPaymentActions . "\n  "
            . $key . ': ' . implode(",  ", $supportedPaymentActionsPerPaymentMethod[$key]);
    }

    echo <<<END_USAGE
Usage: php configure_payment_method_db.php <paymentmethod>

Supported payment methods:
  $supportedPaymentMethods
Supported operations:
  $supportedPaymentActions


END_USAGE;
    exit(1);
}
$paymentMethod = trim($argv[1]);
$paymentAction = trim($argv[2]);

$dbConfig = buildConfigByPaymentMethod($paymentMethod, $paymentAction, $gateway);
if (empty($dbConfig)) {
    echo "Payment method $paymentMethod is not supported\n";
    exit(1);
}

if (!in_array($paymentAction, $supportedPaymentActionsPerPaymentMethod[$paymentMethod])) {
    echo "Payment action $paymentAction is not supported\n";
    exit(1);
}

updatePrestashopEeDbConfig($dbConfig, $paymentMethod);

/**
 * Method buildConfigByPaymentMethod
 * @param string $paymentMethod
 * @param string $paymentAction
 * @param string $gateway
 * @return array
 *
 * @since   1.3.4
 */

function buildConfigByPaymentMethod($paymentMethod, $paymentAction, $gateway)
{
    global $defaultConfig;

    if (!array_key_exists($paymentMethod, $defaultConfig)) {
        return null;
    }
    $config = $defaultConfig[$paymentMethod];

    $config['payment_action'] = $paymentAction;
    $jsonFile = GATEWAY_CONFIG_PATH . DIRECTORY_SEPARATOR . $paymentMethod . '.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile));
        if (!empty($jsonData) && !empty($jsonData->$gateway)) {
            foreach (get_object_vars($jsonData->$gateway) as $key => $data) {
                // only replace values from json if the key is defined in defaultDbValues
                if (array_key_exists($key, $config)) {
                    $config[$key] = $data;
                }
            }
        }
    }
    $config['payment_action'] = $paymentAction;
    return $config;
}

/**
 * Method updatePrestashopEeDbConfig
 * @param array $db_config
 * @param string $payment_method
 * @return boolean
 *
 * @since   1.3.4
 */
function updatePrestashopEeDbConfig($db_config, $payment_method)
{
    echo 'Configuring ' . $payment_method . " payment method in the shop system \n";
    //DB setup
    $dbHost = getenv('PRESTASHOP_DB_SERVER');
    $dbName = getenv('PRESTASHOP_DB_NAME');
    $dbUser = 'root';
    $dbPass = getenv('PRESTASHOP_DB_PASSWORD');
    $dbPort = getenv('MYSQL_PORT_IN');
    // table name
    $tableName = 'ps_configuration';
    $paymentMethodCardPrefix = 'WIRECARD_PAYMENT_GATEWAY_' . strtoupper($payment_method) . '_';

    // create connection
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    if ($mysqli->connect_errno) {
        echo "Can't connect DB $dbName on host $dbHost as user $dbUser \n";
        return false;
    }
    foreach ($db_config as $name => $value) {
        $fullName = $paymentMethodCardPrefix . strtoupper($name);
        // remove existing config if any exists - or do nothing
        $stmtDelete = $mysqli->prepare("DELETE FROM $tableName WHERE name = ?");
        $stmtDelete->bind_param('s', $fullName);
        $stmtDelete->execute();

        // insert the new config
        $stmtInsert = $mysqli->prepare("INSERT INTO $tableName (name, value, date_add, date_upd) VALUES (?, ?, now(), now())");
        $stmtInsert->bind_param('ss', $fullName, $value);
        $stmtInsert->execute();
    }
    return true;
}
