<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

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

    public function getLanguages()
    {
        return new Language();
    }
}
