<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
            case 'WIRECARD_PAYMENT_GATEWAY_SEPA_PAYMENT_ACTION':
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_PAYMENT_ACTION':
            case 'WIRECARD_PAYMENT_GATEWAY_PAYPAL_PAYMENT_ACTION':
                return 'reserve';
                break;

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
            case 'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_REQUESTOR_CHALLENGE':
                return '02';

            default:
                return $param;
        }
    }

    public static function updateGlobalValue($key, $values, $html = false)
    {
        return true;
    }

    public static function deleteByName($string)
    {
        return true;
    }
}
