<?php
class PaymentModule
{
    protected $name;

    protected $_html;
    protected $html;
    protected $identifier;
    protected $context;

    public function __construct()
    {
        $this->_html = null;
        $this->html = null;
        $this->identifier = 1;
        $this->context = new Context();
    }

    public function install()
    {
        if(!strlen($this->name)){
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if(!strlen($this->name)){
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
}