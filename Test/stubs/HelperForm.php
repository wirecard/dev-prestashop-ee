<?php
namespace Wirecard\Prestashop;

class HelperForm
{
    public function generateForm($fields)
    {
        return json_encode($fields);
    }
}
