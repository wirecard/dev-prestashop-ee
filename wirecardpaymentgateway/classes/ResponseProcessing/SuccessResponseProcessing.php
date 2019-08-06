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

use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Helper\ModuleHelper;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * Class SuccessResponseProcessing
 * @package WirecardEE\Prestashop\classes\ResponseProcessing
 * @since 2.1.0
 */
final class SuccessResponseProcessing implements ResponseProcessing
{
    use ModuleHelper;

    /**
     * @param SuccessResponse $response
     * @param int $order_id
     * @since 2.1.0
     */
    public function process($response, $order_id)
    {
        $order = new \Order((int) $order_id);
        $cart = $this->getCartIdFromOrder($order_id);
        $customer = new \Customer((int) $cart->id_customer);

        if ($this->isOrderStarting($order)) {
            $this->updateOrderTo($order, OrderManager::WIRECARD_OS_AWAITING);
            $this->updateOrderPayments($order, $response);
        }

        //@TODO think of a better implementation of the POI/PIA data to be set and displayed in checkout

        $this->redirectToSuccessCheckoutPage(
            $cart->id,
            $this->getModuleId(),
            $order_id,
            $customer->secure_key
        );
    }

    /**
     * @param \Order $order
     * @param SuccessResponse $response
     * @since 2.1.0
     */
    private function updateOrderPayments($order, $response)
    {
        $order_payments = \OrderPayment::getByOrderReference($order->reference);

        if (!empty($order_payments)) {
            $order_payments[count($order_payments) - 1]->transaction_id = $response->getTransactionId();
            $order_payments[count($order_payments) - 1]->save();
        }
    }

    /**
     * @param \Order $order
     * @param string $status
     * @since 2.1.0
     */
    private function updateOrderTo($order, $status)
    {
        $order->setCurrentState(\Configuration::get($status));
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

    /**
     * @param int $order_id
     * @return \Cart
     * @since 2.1.0
     */
    private function getCartIdFromOrder($order_id)
    {
        return \Cart::getCartByOrderId($order_id);
    }

    /**
     * Redirect to the success checkout page
     * @param string $cart_id
     * @param string $module_id
     * @param string $order_id
     * @param string $customer_secure_key
     * @since 2.1.0
     */
    private function redirectToSuccessCheckoutPage($cart_id, $module_id, $order_id, $customer_secure_key)
    {
        \Tools::redirect('index.php?controller=order-confirmation&id_cart='
                         .$cart_id.'&id_module='
                         .$module_id.'&id_order='
                         .$order_id.'&key='
                         .$customer_secure_key);
    }
}
