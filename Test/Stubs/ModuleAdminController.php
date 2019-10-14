<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
        $this->object->ordernumber = 'ABCDEFG';
        $this->object->response = '{"key": "value"}';
        $this->object->transaction_id = '12l3j123kjg12kj3g123';
        $this->object->transaction_type = 'authorization';
        $this->object->transaction_state = 'open';
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
