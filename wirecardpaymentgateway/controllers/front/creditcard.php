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
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv3
 */

use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Models\CreditCardVault;

/**
 * @property WirecardPaymentGateway module
 *
 * @since 1.1.0
 */
class WirecardPaymentGatewayCreditCardModuleFrontController extends ModuleFrontController
{
    /**
     * @var CreditCardVault $vaultModel
     */
    private $vaultModel;

    public function initContent() {
        $this->ajax = true;
        $this->vaultModel = new CreditCardVault($this->context->customer->id);
        parent::initContent();
    }

    public function postProcess() {
        $orderId = \Tools::getValue('orderId');
        $payload = \Tools::getValue('payload');
        $paymentMethod = $payload['payment_method'];
        $paymentState = $payload['transaction_state'];
        $payment = $this->module->getPaymentFromType($paymentMethod);
        $config = $payment->createPaymentConfig($this->module);
        $transactionService = new TransactionService($config, new WirecardLogger());

        $order = new Order($orderId);
        $cartId = $order->id_cart;
        $cart = new Cart((int) ($cartId));
        $customer = new Customer($cart->id_customer);
        $response = $transactionService->processJsResponse($payload, '');


        if ($paymentState == 'success') {
            if (($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING))) {
                $order->setCurrentState(Configuration::get(OrderManager::WIRECARD_OS_AWAITING));
            }
            $orderPayments = OrderPayment::getByOrderReference($order->reference);
            if (!empty($orderPayments)) {
                $orderPayments[count($orderPayments) - 1]->transaction_id = $response->getTransactionId();
                $orderPayments[count($orderPayments) - 1]->save();
            }
            header('Content-Type: application/json; charset=utf8');

            $url = __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart='
                .$cart->id.'&id_module='
                .$this->module->id.'&id_order='
                .$this->module->currentOrder.'&key='
                .$customer->secure_key;

            die(json_encode([ "url" => $url]));
        } else {
            if ($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
                $order->setCurrentState(_PS_OS_ERROR_);
                $params = array(
                    'submitReorder' => true,
                    'id_order'      => (int) $orderId
                );
                $url = $this->context->link->getPageLink('order', true, $order->id_lang, $params);
                if ($paymentState == 'cancel') {
                    $this->errors = $this->l('canceled_payment_process');
                }
            } else {
                $this->errors = $this->l('order_error');
            }
            header('Content-Type: application/json; charset=utf8');
            die(json_encode([ "url" => $url]));
        }
    }

    /**
     * list user credit cards from the vault
     *
     * @since 1.1.0
     */
    public function displayAjaxListStoredCards() {
        header('Content-Type: application/json; charset=utf8');
        die(json_encode($this->vaultModel->getUserCards()));
    }

    /**
     * add a card and return a list of stored user credit cards
     *
     * @since 1.1.0
     */
    public function displayAjaxAddCard() {
        $tokenId = Tools::getValue('tokenid');
        $maskedpan = Tools::getValue('maskedpan');

        if (!$tokenId || !$maskedpan) {
            $this->displayAjaxListStoredCards();
        }

        $this->vaultModel->addCard($maskedpan, $tokenId);

        $this->displayAjaxListStoredCards();
    }

    /**
     * delete a card and return a list of stored user credit cards
     *
     * @since 1.1.0
     */
    public function displayAjaxDeleteCard() {
        $ccid = Tools::getValue('ccid');

        if (!$ccid) {
            $this->displayAjaxListStoredCards();
        }

        $this->vaultModel->deleteCard($ccid);

        $this->displayAjaxListStoredCards();
    }
}
