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

namespace WirecardEE\Prestashop\classes\ResponseProcessing;

use Wirecard\PaymentSdk\Response\FailureResponse;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * Class FailureResponseProcessing
 * @package WirecardEE\Prestashop\classes\ResponseProcessing
 * @since 2.1.0
 */
final class FailureResponseProcessing implements ResponseProcessing
{
    /**
     * @param FailureResponse $response
     * @param int $order_id
     * @since 2.1.0
     */
    public function process($response, $order_id)
    {
        $order = new \Order((int) $order_id);

        $errors = $this->getErrorsFromStatusCollection($response->getStatusCollection());

        $context = \Context::getContext();
        $context->controller->errors = $errors;

        if ($this->isOrderStarting($order)) {
            $order->setCurrentState(\Configuration::get('PS_OS_ERROR'));

            $original_cart = \Cart::getCartByOrderId($order_id);
            $cart_clone = $original_cart->duplicate()['cart'];
            $this->saveCartToSession($cart_clone);

            $context->controller->redirectWithNotifications('index.php?controller=order');
        }
    }

    private function saveCartToSession($cart_clone)
    {
        $context = \Context::getContext();
        $context->cart = $cart_clone;
        $context->id_cart = $cart_clone->id;
        $context->cookie->id_cart = $cart_clone->id;
    }

    private function getErrorsFromStatusCollection($statuses)
    {
        $error = array();

        foreach ($statuses->getIterator() as $status) {
            array_push($error, $status->getDescription());
        }

        return $error;
    }

    /**
     * @param \Order $order
     * @return bool
     * @since 2.1.0
     */
    private function isOrderStarting($order)
    {
        if ($order->current_state === \Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            return true;
        }

        return false;
    }
}
