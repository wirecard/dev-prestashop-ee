<?php

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
}
