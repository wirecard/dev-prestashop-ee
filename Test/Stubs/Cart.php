<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
        $this->id = $id;
    }


    public static function getCartByOrderId($id) {
        return new self(12345);
    }

    public function getProducts()
    {
        if (123 === $this->id) {
            return [
                0 => [
                    'id_product' => 1,
                    'cart_quantity' => 1,
                    'total_wt' => 2,
                    'name' => 'Product 1',
                    'total' => 100,
                    'description_short' => 'short desc',
                    'reference' => 'reference']
            ];
        }
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
