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

use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\UpiTransaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use WirecardEE\Prestashop\Helper\AdditionalInformation;
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

    /**
     * Implementation of initContent.
     */
    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
    }

    /**
     * Implementation of postProcess function
     *
     * @return bool|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $this->vaultModel = new CreditCardVault($this->context->customer->id);
        // If its ajax call don't do anything
        if (\Tools::getValue('ajax')) {
            return true;
        }

        $orderId = \Tools::getValue('orderId');
        $order = new Order($orderId);
        // If is cancel submit cancel Order
        if (\Tools::getValue('cancel')) {
            $this->cancelOrder($order);
        }

        $saveCard = \Tools::getValue('saveCard');
        $tokenId = \Tools::getValue('tokenId');
        $textPayload = html_entity_decode(\Tools::getValue('payload'));
        $payload = json_decode($textPayload, true);
        $paymentType = $payload['payment_method'];
        $ccVaultEnable = $this->module->getConfigValue($paymentType, 'ccvault_enabled');
        if ('true' === $saveCard && $ccVaultEnable) {
            $this->addCard($payload);
        }

        $transactionService = $this->getTransactionService($paymentType);
        $url = $this->module->createRedirectUrl($orderId, $paymentType, 'success');

        if ('new' !== $tokenId) {
            $response = $this->pay($payload, $tokenId, $url, $transactionService);
        } else {
            $response = $transactionService->processJsResponse($payload, $url);
        }

        $this->processResponse($response, $order, $url);
    }

    /**
     * Get Transaction Service
     *
     * @param string $paymentMethod
     * @return TransactionService
     */
    private function getTransactionService($paymentMethod)
    {
        /** @var Payment $payment */
        $payment = $this->module->getPaymentFromType($paymentMethod);
        $config = $payment->createPaymentConfig($this->module);
        return new TransactionService($config, new WirecardLogger());
    }

    /**
     * Process response function.
     *
     * @param SuccessResponse | FormInteractionResponse $response
     * @param                                           $order
     * @param                                           $url
     */
    private function processResponse($response, $order, $url)
    {
        if ($response instanceof SuccessResponse) {
            $this->saveOrder($order, $response, $url);
        } elseif ($response instanceof FormInteractionResponse) {
            $this->createPostForm($response);
        } else {
            $this->cancelOrder($order);
        }
    }

    /**
     * Pay function
     *
     * @param array              $payload
     * @param string             $tokenId
     * @param string             $url
     * @param TransactionService $transactionService
     * @return mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function pay($payload, $tokenId, $url, $transactionService)
    {
        $amount = new Amount($payload['requested_amount'], $payload['requested_amount_currency']);
        $paymentType = $payload['payment_method'];

        if (UpiTransaction::NAME === $paymentType) {
            $transaction = new UpiTransaction();
        } else {
            $transaction = new CreditCardTransaction();
        }
        $transaction->setAmount($amount);
        $transaction->setTokenId($tokenId);
        $transaction->setTermUrl($url);
        $additionalInformation = new AdditionalInformation();
        $orderId = $payload['order_number'];
        $order = new Order($orderId);
        $cart = new Cart($order->id_cart);
        $currency = new Currency($cart->id_currency);
        $redirectUrls = new Redirect(
            $this->module->createRedirectUrl($cart->id, CreditCardTransaction::NAME, 'success'),
            $this->module->createRedirectUrl($cart->id, CreditCardTransaction::NAME, 'cancel'),
            $this->module->createRedirectUrl($cart->id, CreditCardTransaction::NAME, 'failure')
        );
        $transaction->setNotificationUrl($this->module->createNotificationUrl($cart->id, $paymentType));
        $transaction->setRedirect($redirectUrls);
        $customFields = new CustomFieldCollection();
        $customFields->add(new CustomField('orderId', $orderId));
        $transaction->setCustomFields($customFields);

        if ($this->module->getConfigValue($paymentType, 'shopping_basket')) {
            $transaction->setBasket($additionalInformation->createBasket(
                $cart,
                $transaction,
                $currency->iso_code
            ));
        }

        if ($this->module->getConfigValue($paymentType, 'descriptor')) {
            $transaction->setDescriptor($additionalInformation->createDescriptor($orderId));
        }

        if ($this->module->getConfigValue($paymentType, 'send_additional')) {
            $transaction = $additionalInformation->createAdditionalInformation(
                $cart,
                $orderId,
                $transaction,
                $currency->iso_code
            );
        }
        if ('purchase' === $payload['transaction_type']) {
            $response = $transactionService->pay($transaction);
        } else {
            $response = $transactionService->reserve($transaction);
        }
        $ccVaultEnable = $this->module->getConfigValue($payload['payment_method'], 'ccvault_enabled');
        if ($ccVaultEnable) {
            $this->vaultModel->updateLastUsed($tokenId);
        }
        return $response;
    }

    /**
     * Save order.
     *
     * @param Order           $order
     * @param SuccessResponse $response
     * @param string          $url
     */
    private function saveOrder($order, $response, $url)
    {
        if (($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING))) {
            $order->setCurrentState(Configuration::get(OrderManager::WIRECARD_OS_AWAITING));
        }
        $orderPayments = OrderPayment::getByOrderReference($order->reference);
        if (!empty($orderPayments)) {
            $orderPayments[count($orderPayments) - 1]->transaction_id = $response->getTransactionId();
            $orderPayments[count($orderPayments) - 1]->save();
        }
        Tools::redirect($url);
    }

    /**
     * Cancel order.
     *
     * @param Order $order
     */
    private function cancelOrder($order)
    {
        if ($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            $order->setCurrentState(_PS_OS_ERROR_);
            $this->errors = $this->module->l('canceled_payment_process');
        } else {
            $this->errors = $this->module->l('order_error');
        }
        $params = [
            'submitReorder' => true,
            'id_order' => (int)$order->id
        ];
        Tools::redirect($this->context->link->getPageLink('order', true, $order->id_lang, $params));
    }

    /**
     * Delete a card
     *
     * @since 1.1.0
     */
    public function displayAjaxDeleteCard()
    {
        $ccid = Tools::getValue('ccid');
        $success = $this->vaultModel->deleteCard($ccid);
        header('Content-Type: application/json; charset=utf8');
        die(json_encode(['succes' => $success]));
    }

    /**
     * Add Card to vault
     *
     * @param array $payload
     */
    private function addCard($payload)
    {
        $token = $payload['token_id'];
        $maskedPan = $payload['masked_account_number'];
        $this->vaultModel->addCard($maskedPan, $token);
    }

    /**
     * Create post form for credit card
     *
     * @param array $data
     * @return string
     * @since 1.0.0
     */
    private function createPostForm($response)
    {
        $data = null;
        $data['url'] = $response->getUrl();
        $data['method'] = $response->getMethod();
        $data['form_fields'] = $response->getFormFields();
        $logger = new WirecardLogger();
        try {
            $this->context->smarty->assign($data);
            die($this->context->smarty->fetch(_PS_MODULE_DIR_ . 'wirecardpaymentgateway' . DIRECTORY_SEPARATOR .
                'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR .
                'creditcard_submitform.tpl'));
        } catch (SmartyException $e) {
            $logger->error($e->getMessage());
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
        return '';
    }
}
