<?php

class Controller
{
    protected $context;

    protected $template;

    public $controller_type;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->context->controller = $this;
    }

    public function redirectWithNotifications($redirect)
    {
        Tools::redirect($redirect);
    }
}
