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

class Cart
{
    private $products;
    private $shipping;
    public $id_customer;
    public $id;
    private $amount;
    public $id_currency;
    public $id_address_invoice;
    public $id_address_delivery;
    public $secure_key;

    public function __construct($id = null)
    {
        if(!is_null($id)) {
            $this->id_customer = 1;
        }

        $this->amount = 20;
        $this->id = 102;
    }


    public static function getCartByOrderId($id) {
        return new self(12345);
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products)
    {
        $this->products = $products;
    }

    public function getTotalShippingCost($val, $bool)
    {
        return $this->shipping;
    }

    public function getOrderTotal()
    {
        return $this->amount;
    }

    public function setOrderTotal($amount)
    {
        $this->amount = $amount;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setAddress($type, $val = null)
    {
        switch ($type) {
            case 'delivery':
                $this->id_address_delivery = new Address();
                break;
            case 'invoice':
            default:
                $this->id_address_invoice = new Address();
        }
    }

    public function isVirtualCart()
    {
        return false;
    }
}
