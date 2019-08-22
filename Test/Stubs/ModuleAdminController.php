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

class ModuleAdminController extends Controller
{
    static $currentIndex = '1';

    public $module;

    public $object;

    public function __construct()
    {
        parent::__construct();
        $this->module = Module::getInstanceByName('wirecardpaymentgateway');
        $this->controller_type = 'moduleadmin';

        $this->object = new stdClass();
        $this->object->paymentmethod = 'creditcard';
        $this->object->response = '{key: value}';
        $this->object->transaction_id = '12l3j123kjg12kj3g123';
        $this->object->transaction_type = 'authorization';
        $this->object->transaction_state = 'success';
        $this->object->amount = '20';
        $this->object->currency = 'EUR';
        $this->object->tx_id = '11';
    }

    public function getLanguages()
    {
        return new Language();
    }

    protected function l($string, $specific = false)
    {
        if (isset($this->module) && is_a($this->module, 'Module')) {
            return $this->module->l($string, $specific);
        } else {
            return $string;
        }
    }

    public function addRowAction($string)
    {
        return $string;
    }

    public function renderView()
    {
        return;
    }
}
