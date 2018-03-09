<?php

class Cart
{
    private $products;
    private $shipping;
    public $id_customer;

    public function getProducts()
    {
        return $this->products;
    }

    public function getTotalShippingCost($val, $bool)
    {
        return $this->shipping;
    }
}
