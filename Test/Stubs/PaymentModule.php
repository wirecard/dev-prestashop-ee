<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

require_once __DIR__.'/Translator.php';

class PaymentModule extends Module
{
    public function install()
    {
        if (!strlen($this->name)) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!strlen($this->name)) {
            return false;
        }
        return true;
    }

    public function l($string)
    {
        return $string;
    }

    public function setName($string)
    {
        $this->name = $string;
    }

    public function displayConfirmation($string)
    {
        return $string;
    }

    public function displayWarning($string)
    {
        return $string;
    }

    public function display($file, $path)
    {
        return $file . $path;
    }

    public function registerHook($string)
    {
        return true;
    }

    public function displayError($string)
    {
        return $string;
    }

    public function fetch($string)
    {
        return $string;
    }

    public function getTranslator()
    {
        return new Translator();
    }
}
