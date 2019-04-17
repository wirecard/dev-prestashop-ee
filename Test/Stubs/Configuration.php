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

class Configuration
{
    private static $basket;
    private static $additional;

    public static function setBasketConfig($bool)
    {
        self::$basket = $bool;
    }

    public static function setAdditionalConfig($bool)
    {
        self::$additional = $bool;
    }

    public static function updateValue($param, $value)
    {
        return true;
    }

    public static function get($param)
    {
        switch ($param) {
            case 'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_PAYMENT_ACTION':
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_PAYMENT_ACTION':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_PAYMENT_ACTION':
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_PAYMENT_ACTION':
                return 'reserve';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_BASE_URL':
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_BASE_URL':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_BASE_URL':
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_BASE_URL':
                return 'https://api-test.wirecard.com';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_HTTP_USER':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_HTTP_USER':
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_HTTP_USER':
                return '70000-APITEST-AP';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_HTTP_PASS':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_HTTP_PASS':
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_HTTP_PASS':
                return 'qD2wzQ_hrc!8';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_MERCHANT_ACCOUNT_ID':
                return '2a0e9351-24ed-4110-9a1b-fd0fee6bec26';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_MERCHANT_ACCOUNT_ID':
                return '4c901196-eff7-411e-82a3-5ef6b6860d64';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_MERCHANT_ACCOUNT_ID':
                return '53f2895a-e4de-4e82-a813-0d87a10e55e6';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_UNIONPAYINTERNATIONAL_MERCHANT_ACCOUNT_ID':
                return 'c6e9331c-5c1f-4fc6-8a08-ef65ce09ddb0';
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_THREE_D_MERCHANT_ACCOUNT_ID':
                return '508b8896-b37d-4614-845c-26bf8bf2c948';
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_THREE_D_SECRET':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_SECRET':
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_SECRET':
                return 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_SECRET':
                return 'ecdf5990-0372-47cd-a55d-037dccfe9d25';
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_SEND_ADDITIONAL':
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_SEND_ADDITIONAL':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_SEND_ADDITIONAL':
                if (self::$additional) {
                    return 1;
                } else {
                    return 0;
                }
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_SHOPPING_BASKET':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_SHOPPING_BASKET':
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_SHOPPING_BASKET':
                if (self::$basket) {
                    return 1;
                } else {
                    return 0;
                }
                break;
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_DESCRIPTOR':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_DESCRIPTOR':
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_DESCRIPTOR':
                return 1;
                break;
            case 'WIRECARD_OS_AUTHORIZATION':
                return 0;
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_SSL_MAX_LIMIT':
                return 50;
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_THREE_D_MIN_LIMIT':
                return 150;
            default:
                return $param;
        }
    }

    public static function deleteByName($string)
    {
        return true;
    }
}
