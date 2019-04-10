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

use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
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

        if(\Tools::getValue('ajax')){
            return true;
        }
        $orderId = \Tools::getValue('orderId');
        $saveCard = \Tools::getValue('saveCard');
        $tokenId = \Tools::getValue('tokenId');
        $textPayload = html_entity_decode(\Tools::getValue('payload'));
        $payload = json_decode($textPayload, true);

        if ('on' === $saveCard) {
            $vault = new CreditCardVault($this->context->customer->id);
            $token = $payload['token_id'];
            $maskedPan = $payload['masked_account_number'];
            $vault->addCard($maskedPan, $token);
        }

        $paymentMethod = $payload['payment_method'];
        /** @var Payment $payment */
        $payment = $this->module->getPaymentFromType($paymentMethod);
        $config = $payment->createPaymentConfig($this->module);
        $transactionService = new TransactionService($config, new WirecardLogger());

        $order = new Order($orderId);
        $cartId = $order->id_cart;
        $cart = new Cart((int)($cartId));
        $customer = new Customer($cart->id_customer);
        $params = [
            'id_cart' => $cart->id,
            'id_module' => $this->module->id,
            'id_order' => $orderId,
            'key' => $customer->secure_key
        ];
        $url = $this->context->link->getPageLink('order-confirmation', true, $order->id_lang,
            $params);
        if ('new' !== $tokenId) {
            $amount = new Amount($payload['requested_amount'], $payload['requested_amount_currency']);
            $transaction = new CreditCardTransaction();
            $transaction->setAmount($amount);
            $transaction->setTokenId($tokenId);
            $transaction->setTermUrl($url);
            $response = $transactionService->pay($transaction);
        } else {
            $response = $transactionService->processJsResponse($payload, $url);
        }
        if ($response instanceof SuccessResponse) {
            if (($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING))) {
                $order->setCurrentState(Configuration::get(OrderManager::WIRECARD_OS_AWAITING));
            }
            $orderPayments = OrderPayment::getByOrderReference($order->reference);
            if (!empty($orderPayments)) {
                $orderPayments[count($orderPayments) - 1]->transaction_id = $response->getTransactionId();
                $orderPayments[count($orderPayments) - 1]->save();
            }
        } else if ($response instanceof FormInteractionResponse) {
            $data = null;
            $data['url'] = $response->getUrl();
            $data['method'] = $response->getMethod();
            $data['form_fields'] = $response->getFormFields();
            die($this->createPostForm($data));
        } else {
            if ($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
                $order->setCurrentState(_PS_OS_ERROR_);
                $this->errors = $this->module->l('canceled_payment_process');
            } else {
                $this->errors = $this->module->l('order_error');
            }
            $params = [
                'submitReorder' => true,
                'id_order' => (int)$orderId
            ];
            $url = $this->context->link->getPageLink('order', true, $order->id_lang, $params);
        }
        Tools::redirect($url);
    }

    /**
     * delete a card and return a list of stored user credit cards
     *
     * @since 1.1.0
     */
    public function displayAjaxDeleteCard() {
        $ccid = Tools::getValue('ccid');
        $success = $this->vaultModel->deleteCard($ccid);
        header('Content-Type: application/json; charset=utf8');
        die(json_encode(['succes'=> $success]));
    }

    /**
     * Create post form for credit card
     *
     * @param array $data
     * @return string
     * @since 1.0.0
     */
    private function createPostForm($data) {
        $logger = new WirecardLogger();
        try {
            $this->context->smarty->assign($data);

            return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'wirecardpaymentgateway' . DIRECTORY_SEPARATOR .
                'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR .
                'creditcard_submitform.tpl');
        } catch (SmartyException $e) {
            $logger->error($e->getMessage());
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
        return '';
    }

}
