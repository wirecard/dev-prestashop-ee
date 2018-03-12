<?php

class Configuration
{
    private $config;

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
            return 0;
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_SHOPPING_BASKET' == $param)
        {
            return 0;
        }

        if ('WIRECARD_PAYMENT_GATEWAY_PAYPAL_DESCRIPTOR' == $param)
        {
            return 0;
        }

        return $param;
    }
}
