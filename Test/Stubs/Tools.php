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

class Tools
{
    static $paymentType = 'paypal';
    static $action = 'cancel';

    public static function isSubmit($string)
    {
        return true;
    }

    public static function strtoupper($string)
    {
        return strtoupper($string);
    }

    public static function strtolower($string)
    {
        return strtolower($string);
    }

    public static function getValue($string)
    {
        switch ($string) {
            case 'paymentType':
                return self::$paymentType;
                break;
            case 'action':
                return self::$action;
                break;
            default:
                return $string;
        }
    }

    public static function getAllValues()
    {
        return array();
    }

    public static function getAdminTokenLite($string)
    {
        return $string;
    }

    public static function substr($string, $start, $length = null)
    {
        return substr($string, $start, $length);
    }

    public static function strlen($string)
    {
        return strlen($string);
    }

    public static function redirect($string)
    {
        if (strlen($string)) {
            return true;
        }
        return false;
    }

    public static function jsonEncode($var)
    {
        return json_encode($var);
    }

    public static function jsonDecode($var)
    {
        return json_decode($var);
    }

    public static function file_get_contents($var)
    {
        return file_get_contents($var);
    }

    public static function ps_round($value, $precision, $mode = PHP_ROUND_HALF_UP)
    {
        return round($value, $precision, $mode);
    }
}
