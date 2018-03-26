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

use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

/**
 * Class WirecardPaymentGatewayReturnModuleFrontController
 *
 * @extends ModuleFrontController
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayReturnModuleFrontController extends ModuleFrontController
{
    /**
     * Process redirects and responses
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        $response = $_REQUEST;
        $paymentType = Tools::getValue('payment_type');
        $paymentState = Tools::getValue('payment_state');
        $payment = $this->module->getPaymentFromType($paymentType);
        $config = $payment->createPaymentConfig($this->module);

        if ($paymentState == 'success') {
            try {
                $transactionService = new TransactionService($config, new WirecardLogger());
                $result = $transactionService->handleResponse($response);
                if ($result instanceof SuccessResponse) {
                    $this->processSuccess($result);
                } elseif ($result instanceof FailureResponse) {
                    $errors = "";
                    foreach ($result->getStatusCollection()->getIterator() as $item) {
                        $errors .= $item->getDescription() . "<br>";
                    }
                    $this->errors = $errors;
                    $this->redirectWithNotifications($this->context->link->getPageLink('order'));
                }
            } catch (\InvalidArgumentException $exception) {
                $this->errors = 'Invalid Argument: ' . $exception->getMessage();
                $this->redirectWithNotifications($this->context->link->getPageLink('order'));
            } catch (\MalformedResponseException $exception) {
                $this->errors = 'Malformed Response: ' . $exception->getMessage();
                $this->redirectWithNotifications($this->context->link->getPageLink('order'));
            } catch (Exception $exception) {
                $this->errors = $exception->getMessage();
                $this->redirectWithNotifications($this->context->link->getPageLink('order'));
            }
        } else {
            $cartId = Tools::getValue('id_cart');
            $logger = new WirecardLogger();
            $logger->error('CartId: '.print_r($cartId, true));
            $orderId = Order::getIdByCartId((int)$cartId);
            $logger->error('OrderId: '.print_r($orderId, true));
            $order = new Order($orderId);
            $order->delete();

            if ($paymentState == 'cancel') {
                $this->errors = 'You have canceled the payment process.';
            } else {
                $this->errors = 'Something went wrong during the payment process.';
            }
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
    }

    /**
     * Create order and redirect for success response
     *
     * @param SuccessResponse $response
     * @since 1.0.0
     */
    public function processSuccess($response)
    {
        $cartId = $response->getCustomFields()->get('orderId');
        $cart = new Cart((int)($cartId));
        $orderId = Order::getIdByCartId((int)$cartId);
        $customer = new Customer($cart->id_customer);
        $order = new Order($orderId);

        $orderPayments = OrderPayment::getByOrderReference($order->reference);
        if (!empty($orderPayments)) {
            $orderPayments[count($orderPayments) - 1]->transaction_id = $response->getTransactionId();
            $orderPayments[count($orderPayments) - 1]->save();
        }
        Tools::redirect('index.php?controller=order-confirmation&id_cart='
            .$cart->id.'&id_module='
            .$this->module->id.'&id_order='
            .$this->module->currentOrder.'&key='
            .$customer->secure_key);
    }
}
