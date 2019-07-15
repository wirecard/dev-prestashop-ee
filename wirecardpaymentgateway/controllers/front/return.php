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
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\OrderManager;
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
    use WirecardEE\Prestashop\Helper\TranslationHelper;

    /** @var string  */
    const TRANSLATION_FILE = "return";

    /** @var Order */
    private $order;

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
        $orderId = Tools::getValue('id_order');

        $this->order = new Order($orderId);

        switch ($paymentState) {
            case "success":
                $this->processSuccess($response, $paymentType);
                break;
            case "cancel":
                $this->processCancel($orderId);
                break;
            case "failure":
                $this->processFailure($orderId);
                break;
        }
    }

    /**
     * The redirect was successful we need to process the response
     *
     * @param array $response
     * @param string $paymentType
     * @since 2.0.0
     */
    public function processSuccess($response, $paymentType)
    {
        $response = $this->processResponseWithTransactionService($response, $paymentType);

        if ($response instanceof SuccessResponse) {
            $this->updateOrder($response->getTransactionId());
            $this->setCookieForSuccessPage($response);
            $this->redirectToSuccessPage();
        }

        //$this->processFailure($this->order->id);
        //@TODO error processing and failed transaction
        $errors = "";
        foreach ($response->getStatusCollection()->getIterator() as $item) {
            $errors .= $item->getDescription() . "<br>";
        }
        $this->errors = $errors;
        $this->redirectWithNotifications($this->context->link->getPageLink('order'));
    }

    /**
     * Update the order after success
     *
     * @param string $transactionId
     * @since 2.0.0
     */
    private function updateOrder($transactionId)
    {
        if ($this->order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            $this->order->setCurrentState(Configuration::get(OrderManager::WIRECARD_OS_AWAITING));
        }

        $orderPayments = OrderPayment::getByOrderReference($this->order->reference);
        if (!empty($orderPayments)) {
            $orderPayments[count($orderPayments) - 1]->transaction_id = $transactionId;
            $orderPayments[count($orderPayments) - 1]->save();
        }
    }

    /**
     * Set the cookie for the frontend to display pia data
     *
     * @param $response
     * @since 2.0.0
     */
    private function setCookieForSuccessPage($response)
    {
        $this->context->cookie->__set('pia-enabled', false);

        if ($response->getPaymentMethod() == 'wiretransfer' &&
            $this->module->getConfigValue('poipia', 'payment_type') == 'pia') {
            $data = $response->getData();
            $this->context->cookie->__set('pia-enabled', true);
            $this->context->cookie->__set('pia-iban', $data['merchant-bank-account.0.iban']);
            $this->context->cookie->__set('pia-bic', $data['merchant-bank-account.0.bic']);
            $this->context->cookie->__set('pia-reference-id', $data['provider-transaction-reference-id']);
        }
    }

    /**
     * Redirect to the thank you page
     *
     * @since 2.0.0
     */
    private function redirectToSuccessPage()
    {
        $customer = new Customer($this->cart->id_customer);
        Tools::redirect('index.php?controller=order-confirmation&id_cart='
            .$this->cart->id.'&id_module='
            .$this->module->id.'&id_order='
            .$this->order->id.'&key='
            .$customer->secure_key);
    }

    /**
     * Process the failed order redirect
     *
     * @param string $orderId
     * @since 2.0.0
     */
    public function processFailure($orderId)
    {
        if ($this->order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            $this->order->setCurrentState(_PS_OS_ERROR_);
            $params = array(
                'submitReorder' => true,
                'id_order' => (int)$orderId
            );
        } else {
            $this->errors = $this->l('order_error');
        }
        $this->redirectWithNotifications(
            $this->context->link->getPageLink('order', true, $this->order->id_lang, $params)
        );
    }

    /**
     * Process the cancel order redirect
     *
     * @param $orderId
     * @since 2.0.0
     */
    public function processCancel($orderId)
    {
        $params = array(
            'submitReorder' => true,
            'id_order' => (int)$orderId
        );

        $this->errors = $this->l('canceled_payment_process');
        $this->redirectWithNotifications(
            $this->context->link->getPageLink('order', true, $this->order->id_lang, $params)
        );
    }

    /**
     * Process the response with the paymentSDK
     *
     * @param array $response
     * @param string $paymentType
     * @return \Wirecard\PaymentSdk\Response\FailureResponse|\Wirecard\PaymentSdk\Response\InteractionResponse|\Wirecard\PaymentSdk\Response\Response|SuccessResponse
     * @since 2.0.0
     */
    private function processResponseWithTransactionService($response, $paymentType)
    {
        $config = $this->getConfigFromPaymentType($paymentType);

        try {
            $transactionService = new TransactionService($config, new WirecardLogger());
            return $transactionService->handleResponse($response);
        } catch (\InvalidArgumentException $exception) {
            $this->errors = 'Invalid Argument: ' . $exception->getMessage();
        } catch (\MalformedResponseException $exception) {
            $this->errors = 'Malformed Response: ' . $exception->getMessage();
        } catch (Exception $exception) {
            $this->errors = $exception->getMessage();
        }

        $this->redirectWithNotifications($this->context->link->getPageLink('order'));
    }

    /**
     * With the payment code load the configuration
     *
     * @param string $paymentType
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 2.0.0
     */
    private function getConfigFromPaymentType($paymentType)
    {
        $payment = $this->module->getPaymentFromType($paymentType);
        return $payment->createPaymentConfig($this->module);
    }
}
