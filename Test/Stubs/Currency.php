<?php

class Currency
{
    public $id;
    public $name;
    public $iso_code;

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        $this->id = 1;
        $this->name = 'Euro';
        $this->iso_code = 'EUR';
    }
}
