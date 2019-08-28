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

use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Classes\Engine\NotificationResponse;
use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotificationFactory;

class WirecardPaymentGatewayNotifyModuleFrontController extends ModuleFrontController
{
    /** @var WirecardLogger  */
    public $logger;

    /**
     * WirecardPaymentGatewayNotifyModuleFrontController constructor.
     * @since 2.1.0
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = new WirecardLogger();
    }

    /**
     * Process redirects and responses
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        $notification = \Tools::file_get_contents('php://input');

        try {
            $order = $this->getOrder();

            $engine_processing = new NotificationResponse();
            $processed_notify = $engine_processing->process($notification);

            $notify_factory = new ProcessablePaymentNotificationFactory($order, $processed_notify);
            $payment_processing = $notify_factory->getPaymentProcessing();
            $payment_processing->process();
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error in class:'. __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $exception->getMessage()
            );
        }
    }

    /**
     * @return Order
     * @throws \Exception
     * @since 2.1.0
     */
    private function getOrder()
    {
        $order_id = \Tools::getValue('id_order');
        if (\Tools::getValue('payment_type') === 'creditcard') {
            $order_id = $this->getOrderFromCart();
        }

        return new \Order((int) $order_id);
    }

    /**
     * @param int $tick
     * @return int
     * @throws \Exception
     * @since 2.1.0
     */
    private function getOrderFromCart($tick = 1)
    {
        $cart_id = \Tools::getValue('id_cart');
        if ($tick > 30) {
            throw new \Exception('Order with cart id '. $cart_id .' was not mappable');
        }

        /** @var false|int $order_id */
        $order_id = \Order::getIdByCartId($cart_id);

        if ($order_id) {
            return $order_id;
        }

        sleep(1);
        return $this->getOrderFromCart();
    }
}
