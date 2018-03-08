<?php
namespace WirecardEE\Prestashop;

class HelperForm
{
    public function generateForm($fields)
    {
        return json_encode($fields);
    }
}
