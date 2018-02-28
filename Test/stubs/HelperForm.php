<?php
class HelperForm
{
    public function generateForm($fields)
    {
        return json_encode($fields);
    }
}