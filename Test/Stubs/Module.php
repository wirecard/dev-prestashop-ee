<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Module
{
    public static $_INSTANCE;
    private static $modules = array('wirecardpaymentgateway'=>"WirecardPaymentGateway");
    public $name;
    public $context;
    public $smarty;
    public $id;
    public $currentOrder;

    protected $_html;
    protected $html;
    protected $identifier;
    protected $_path;
    public $active;

    public static function getInstanceByName($module)
    {
        if (isset(self::$modules[$module])) {
            $className = self::$modules[$module];
            return new $className();
        }
        return null;
    }

    public function __construct($name = null, Context $context = null)
    {
        $this->context = $context ? $context : Context::getContext();
        /*if (is_object($this->context->smarty)) {
            $this->smarty = $this->context->smarty->createData($this->context->smarty);
        }*/

        // If the module has no name we gave him its id as name
        if ($this->name === null) {
            $this->name = $this->id;
        }

        $this->_html = null;
        $this->identifier = 1;
        $this->_path = '/wirecardpaymentgateway';
        $this->active = true;
    }


    public function validateOrder()
    {
        return;
    }

    public function get($serviceName)
    {
        if('prestashop.core.admin.tab.repository' === $serviceName){
            return new Tab();
        }
        return;
    }
}
