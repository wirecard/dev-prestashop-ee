<?php

class Context
{
    public $link;
    public $controller;
    public $language;

    public function __construct()
    {
        $this->link = new Link();
        $this->controller = new ModuleFrontController();
        $this->language = new Language();
    }
}