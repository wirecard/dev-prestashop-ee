<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class ModuleFrontController extends Controller
{
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->module = Module::getInstanceByName('wirecardpaymentgateway');
        $this->controller_type = 'modulefront';
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

    public function setAmount($amount)
    {
        $this->module->context->cart->setOrderTotal($amount);
    }

    public function setProducts($products)
    {
        $this->module->context->cart->setProducts($products);
    }

    public function setCartId($id)
    {
        $this->module->context->cart->setId($id);
    }

    public function setCartAddress($type, $data = null)
    {
        $this->module->context->cart->setAddress($type, $data);
    }

    public function registerJavascript($id, $relativePath, $params = array())
    {
        return;
    }

    public function addJS($string)
    {
        return;
    }

    public function addJquery()
    {
        return;
    }

    public function addJqueryUI($string)
    {
        return;
    }
}
