<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Context
{
    /* @var Context */
    protected static $instance;
    public $link;
    public $controller;
    public $language;
    public $smarty;
    public $cart;
    public $customer;
    public $cookie;
    public $country;
    public $employee;
    public $override_controller_name_for_translations;
    public $currency;
    public $tab;
    public $shop;
    public $mobile_detect;
    public $mode;
    protected $translator = null;

    public function __construct()
    {
        $this->link = new Link();
        $this->language = new Language();
        $this->currency = new Currency();
        $this->smarty = new Smarty();
        $this->cart = new Cart();
        $this->cookie = new Cookie();
    }

    public static function getContext()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Context();
        }

        return self::$instance;
    }

    public function cloneContext()
    {
        return clone($this);
    }
}
