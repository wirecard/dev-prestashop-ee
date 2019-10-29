<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Service;

use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class ContextService
 * @since 2.1.0
 *@package WirecardEE\Prestashop\Helper\Service
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
        if (!$context instanceof \Context) {
            throw new \InvalidArgumentException(
                self::class . ' cannot be initiated as the provided parameter is not type of ' . \Context::class
            );
        }

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

    /**
     * @param string $confirmations
     * @since 2.4.0
     */
    public function setConfirmations($confirmations)
    {
        $this->context->controller->confirmations[] = $confirmations;
    }

    /**
     * @param string $errors
     * @since 2.4.0
     */
    public function setErrors($errors)
    {
        $this->context->controller->errors[] = $errors;
    }

    /**
     * @param SuccessResponse $response
     * @since 2.1.0
     */
    public function setPiaCookie($response)
    {
        $data = $response->getData();

        $this->context->cookie->__set('pia-enabled', true);
        $this->context->cookie->__set('pia-iban', $data['merchant-bank-account.0.iban']);
        $this->context->cookie->__set('pia-bic', $data['merchant-bank-account.0.bic']);
        $this->context->cookie->__set('pia-reference-id', $data['provider-transaction-reference-id']);
    }
}
