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

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_PAYMENT_ACTION' == $param)
        {
            return 'reserve';
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_BASE_URL' == $param)
        {
            return 'https://api-test.wirecard.com';
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_HTTP_USER' == $param)
        {
            return '70000-APITEST-AP';
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_HTTP_PASS' == $param)
        {
            return 'qD2wzQ_hrc!8';
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_MERCHANT_ACCOUNT_ID' == $param)
        {
            return '2a0e9351-24ed-4110-9a1b-fd0fee6bec26';
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_SECRET' == $param)
        {
            return 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_SEND_ADDITIONAL' == $param)
        {
            if (self::$additional) {
                return 1;
            } else {
                return 0;
            }
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_SHOPPING_BASKET' == $param)
        {
            if (self::$basket) {
                return 1;
            } else {
                return 0;
            }
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_DESCRIPTOR' == $param)
        {
            return 1;
        }

        return $param;
    }
}
