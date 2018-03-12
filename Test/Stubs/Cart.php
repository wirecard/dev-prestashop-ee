<?php

class Cart
{
    private $products;
    private $shipping;
    public $id_customer;
    public $id;
    private $amount;
    public $id_currency;

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
}
