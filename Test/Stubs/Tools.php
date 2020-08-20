<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
        if ($length) {
            $result = substr($string, $start, $length);
        } else {
            $result = substr($string, $start);
        }
        return $result;
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

    public static function htmlentitiesDecodeUTF8($string)
    {
        return html_entity_decode($string);
    }

	public static function copy($source, $destination, $stream_context = null)
	{
		return;
	}
}
