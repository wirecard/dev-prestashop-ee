<?php

class Smarty
{
    public function assign($array)
    {
        return implode(", ", $array);
    }
}
