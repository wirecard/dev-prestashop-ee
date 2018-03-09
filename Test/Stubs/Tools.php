<?php

class Tools
{
    public static function isSubmit($string)
    {
        return true;
    }

    public static function strtoupper($string)
    {
        return strtoupper($string);
    }

    public static function getValue($string)
    {
        return $string;
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
}
