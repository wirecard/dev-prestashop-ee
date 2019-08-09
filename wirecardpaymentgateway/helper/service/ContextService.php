<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace WirecardEE\Prestashop\Helper\Service;

/**
 * Class ContextService
 * @package WirecardEE\Prestashop\Helper\Service
 * @since 2.1.0
 */
class ContextService
{
    /** @var \Context */
    private $context;

    /**
     * ContextService constructor.
     *
     * @param \Context $context
     * @since 2.1.0
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * @param \Cart $cart
     * @since 2.1.0
     */
    public function setCart($cart)
    {
        $this->context->cart = $cart;
        $this->context->id_cart = $cart->id;
        $this->context->cookie->id_cart = $cart->id;
    }

    /**
     * @param array $errors
     * @param string $controller_name
     * @since 2.1.0
     */
    public function redirectWithError($errors, $controller_name)
    {
        $this->context->controller->errors = $errors;
        $this->context->controller->redirectWithNotifications($this->context->link->getPageLink($controller_name));
    }

    /**
     * @param string $url
     * @since 2.1.0
     */
    public function redirectWithNotification($url)
    {
        $this->context->controller->redirectWithNotifications($url);
    }

    /**
     * @param string $template_path
     * @param array $data
     * @since 2.1.0
     */
    public function showTemplateWithData($template_path, $data)
    {
        $this->context->smarty->assign($data);
        $this->context->smarty->display($template_path);
    }
}
