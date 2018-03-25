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
use WirecardEE\Prestashop\Models\Transaction;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

class WirecardPaymentGatewayNotifyModuleFrontController extends ModuleFrontController
{
    /**
     * Process redirects and responses
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        $paymentType = Tools::getValue('payment_type');
        $payment = $this->module->getPaymentFromType($paymentType);
        $config = $payment->createPaymentConfig($this->module);
        $notification         = Tools::file_get_contents('php://input');
        $logger = new WirecardLogger();
        try {
            $transactionService = new TransactionService($config, $logger);
            $result = $transactionService->handleNotification($notification);
            if ($result instanceof SuccessResponse && $result->getTransactionType() != 'check-payer-response') {
                $this->processSuccess($result);
            } elseif ($result instanceof FailureResponse) {
                $errors = "";
                foreach ($result->getStatusCollection()->getIterator() as $item) {
                    $errors .= $item->getDescription() . "<br>";
                }
                $logger->error($errors);
                $this->processFailure($result);
            }
        } catch (\InvalidArgumentException $exception) {
            $this->errors = 'Invalid Argument: ' . $exception->getMessage();
            $logger->error($exception->getMessage());
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        } catch (MalformedResponseException $exception) {
            $this->errors = 'Malformed Response: ' . $exception->getMessage();
            $logger->error($exception->getMessage());
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        } catch (Exception $exception) {
            $this->errors = $exception->getMessage();
            $logger->error($exception->getMessage());
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
        $this->errors = 'Something went wrong during the payment process.';
        $this->redirectWithNotifications($this->context->link->getPageLink('order'));
    }

    /**
     * Create/Update order and handle notification
     *
     * @param SuccessResponse $response
     * @since 1.0.0
     */
    private function processSuccess($response)
    {
        $cartId = $response->getCustomFields()->get('orderId');
        $cart = new Cart((int)($cartId));
        $orderId = Order::getIdByCartId((int)$cartId);
        $order = new Order($orderId);
        $orderState = $this->getTransactionOrderState($response);
        $order->setCurrentState($orderState);
        $this->changePaymentStatus($order->reference, $response->getTransactionId(), $orderState);
        $currency = new Currency($cart->id_currency);

        $transaction = Transaction::create(
            $orderId,
            $cartId,
            $cart->getOrderTotal(true),
            $currency->iso_code,
            $response,
            $order->reference
        );

        if (! $transaction) {
            $logger = new WirecardLogger();
            $logger->error('Transaction could not be saved in transaction table');
        }

        $customer = new Customer($cart->id_customer);
        Tools::redirect('index.php?controller=order-confirmation&id_cart='
            .$cart->id.'&id_module='
            .$this->module->id.'&id_order='
            .$this->module->currentOrder.'&key='
            .$customer->secure_key);
    }

    /**
     * Update order for failure response
     *
     * @param FailureResponse $response
     * @since 1.0.0
     */
    private function processFailure($response)
    {
        $cartId = $response->getCustomFields()->get('orderId');
        $orderId = Order::getOrderByCartId((int)$cartId);

        if ($orderId) {
            $order = new Order($orderId);
            $order->setCurrentState('PS_OS_ERROR');
            $orderPayments = OrderPayment::getByOrderReference($order->reference);
            if (!empty($orderPayments)) {
                $orderPayments[0]->transaction_id = $response->getTransactionId();
                $orderPayments[0]->save();
            }
        }
    }

    /**
     * Get order state for specific transactiontype
     *
     * @param \Wirecard\PaymentSdk\Response\Response $response
     * @return integer
     * @since 1.0.0
     */
    private function getTransactionOrderState($response)
    {
        switch ($response->getTransactionType()) {
            case 'authorization':
                return Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION);
            case 'void-authorization':
                return _PS_OS_CANCELED_;
            case 'void-capture':
            case 'refund-capture':
                return _PS_OS_REFUND_;
            case 'debit':
            case 'capture':
            case 'purchase':
            default:
                return _PS_OS_PAYMENT_;
        }
    }

    /**
     * Change payment state of an order
     *
     * @param string $reference
     * @param string $transactionId
     * @param int $orderState
     * @since 1.0.0
     */
    private function changePaymentStatus($reference, $transactionId, $orderState)
    {
        $orderPayments = OrderPayment::getByOrderReference($reference);
        if ($orderState != _PS_OS_CANCELED_&& !empty($orderPayments)) {
            if (count($orderPayments) > 1) {
                $orderPayments[0]->delete();
                $orderPayments[count($orderPayments) - 1]->transaction_id = $transactionId;
                $orderPayments[count($orderPayments) - 1]->save();
            }
        } else {
            $orderPayments[0]->delete();
        }
    }
}
