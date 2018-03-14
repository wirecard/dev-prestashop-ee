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

class Module
{
    public static $_INSTANCE;
    private static $modules = array('wirecardpaymentgateway'=>"WirecardPaymentGateway");
    public $name;
    public $context;
    public $smarty;
    public $id;

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
}