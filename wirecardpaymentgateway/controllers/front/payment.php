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

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use WirecardEE\Prestashop\Helper\SupportedHppLangCode;
use WirecardEE\Prestashop\Helper\AdditionalInformation;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Models\CreditCardVault;
use WirecardEE\Prestashop\Models\Payment;

/**
 * Class WirecardPaymentGatewayPaymentModuleFrontController
 *
 * @extends ModuleFrontController
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayPaymentModuleFrontController extends ModuleFrontController
{

    /**
     * Process payment via transaction service
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        $this->checkCart($cart);

        $paymentType = Tools::getValue('paymentType');
        $orderId = $this->createOrder($cart, $paymentType);

        /** @var Payment $payment */
        $payment = $this->module->getPaymentFromType($paymentType);
        if ($payment) {
            $config = $payment->createPaymentConfig($this->module);
            $operation = $this->module->getConfigValue($paymentType, 'payment_action');
            $transaction = $this->createTransaction($payment, $cart, $paymentType, $orderId);
            if ('creditcard' === $paymentType || 'unionpayinternational' === $paymentType) {
                $data = $this->processCreditCard($config, $transaction, $orderId, $paymentType);
                $vaultData = $this->processVault($paymentType);
                $data = array_merge($data, $vaultData);
                $this->goToCreditCardUi($data);
            }
            $this->executeTransaction($transaction, $config, $operation, $orderId);
        }
    }

    /**
     * Get data for vault.
     * @param string $paymentType
     * @return array
     */
    protected function processVault($paymentType)
    {
        $data = [];
        if ($this->module->getConfigValue($paymentType, 'ccvault_enabled') && Customer::isLogged()) {
            $vault = new CreditCardVault($this->context->customer->id);
            $data['userCards'] = $vault->getUserCards();
            $data['ccvaultenabled'] = true;
        } else {
            $data['userCards'] = [];
            $data['ccvaultenabled'] = false;
        }
        return $data;
    }

    /**
     * Process credit card transaction
     *
     * @param CreditCardConfig|Payment $config
     * @param Transaction              $transaction
     * @param string                   $orderId
     * @param string                   $paymentType
     * @return array
     * @throws SmartyException
     */
    public function processCreditCard($config, $transaction, $orderId, $paymentType)
    {
        $transactionService = new TransactionService($config, new WirecardLogger());
        $paymentConfig = $config->get($paymentType);
        $transaction->setConfig($paymentConfig);
        $transaction->setTermUrl($this->module->createRedirectUrl($orderId, $paymentType, 'success'));
        $paymentAction = $this->module->getConfigValue($paymentType, 'payment_action');
        $baseUrl = $this->module->getConfigValue($paymentType, 'base_url');
        $language = SupportedHppLangCode::getSupportedHppLangCode($baseUrl, $this->context);
        $data['orderId'] = $orderId;
        $data['requestData'] = $transactionService->getCreditCardUiWithData($transaction, $paymentAction, $language);
        $data['paymentPageLoader'] = $baseUrl . '/engine/hpp/paymentPageLoader.js';
        $link = $this->context->link->getModuleLink(
            'wirecardpaymentgateway',
            'creditcard',
            []
        );
        $data['actionUrl'] = $link;
        return $data;
    }

    /**
     * @param Payment $payment
     * @param Cart $cart
     * @param string $paymentType
     * @return Transaction
     */
    public function createTransaction($payment, $cart, $paymentType, $orderId)
    {
        $amount = round($cart->getOrderTotal(), 2);
        $currency = new Currency($cart->id_currency);
        $additionalInformation = new AdditionalInformation();

        $redirectUrls = new Redirect(
            $this->module->createRedirectUrl($cart->id, $paymentType, 'success'),
            $this->module->createRedirectUrl($cart->id, $paymentType, 'cancel'),
            $this->module->createRedirectUrl($cart->id, $paymentType, 'failure')
        );

        /** @var Transaction $transaction */
        $transaction = $payment->createTransaction($this->module, $cart, Tools::getAllValues(), $orderId);
        $transaction->setNotificationUrl($this->module->createNotificationUrl($cart->id, $paymentType));
        $transaction->setRedirect($redirectUrls);
        $transaction->setAmount(new Amount($amount, $currency->iso_code));

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
            $firstName = null;
            $lastName = null;

            if (Tools::getValue('last_name')) {
                $lastName = Tools::getValue('last_name');

                if (Tools::getValue('first_name')) {
                    $firstName = Tools::getValue('first_name');
                }
            }

            $transaction = $additionalInformation->createAdditionalInformation(
                $cart,
                $orderId,
                $transaction,
                $currency->iso_code,
                $firstName,
                $lastName
            );
        }
        return $transaction;
    }

    /**
     * Check if cart values are set
     *
     * @param Cart $cart
     */
    private function checkCart($cart)
    {
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 ||
            !$this->module->active
        ) {
            $this->errors = 'An error occured during the checkout process. Please try again.';
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
        }
    }

    /**
     * Execute transactions with operation pay and reserve
     *
     * @param Transaction                        $transaction
     * @param \Wirecard\PaymentSdk\Config\Config $config
     * @param string                             $operation
     * @param int                                $orderId
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function executeTransaction($transaction, $config, $operation, $orderId)
    {
        $transactionService = new TransactionService($config, new WirecardLogger());
        try {
            /** @var \Wirecard\PaymentSdk\Response\Response $response */
            $response = $transactionService->process($transaction, $operation);
        } catch (Exception $exception) {
            $this->errors = $exception->getMessage();
            $this->processFailure($orderId);
        }

        if ($response instanceof InteractionResponse) {
            $redirect = $response->getRedirectUrl();
            Tools::redirect($redirect);
        } elseif ($response instanceof FailureResponse) {
            $errors = '';
            foreach ($response->getStatusCollection()->getIterator() as $item) {
                $errors .= $item->getDescription();
            }
            $this->errors = $errors;
            $this->processFailure($orderId);
        }
        $this->errors = 'An error occured during the checkout process. Please try again.';
        $this->processFailure($orderId);
    }

    /**
     * Redirect to ui for credit card
     *
     * @param array $data
     * @throws SmartyException
     */
    private function goToCreditCardUi($data)
    {
        $this->setMedia();
        $this->assignGeneralPurposeVariables();
        Media::addJsDef([
            'requestData' => $data['requestData'],
            'orderId' => $data['orderId']
        ]);

        $this->context->controller->registerJavascript(
            'remote-bootstrap',
            $data['paymentPageLoader'],
            ['server' => 'remote', 'position' => 'head', 'priority' => 20]
        );

        $viewsPath = _PS_MODULE_DIR_ . $this->module->name . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR;
        $this->context->controller->addJS(
            $viewsPath . 'js' . DIRECTORY_SEPARATOR . 'creditcard_ui.js'
        );
        $this->context->controller->addCSS(
            $viewsPath . 'css' . DIRECTORY_SEPARATOR . 'app.css'
        );
        $templateVars = [
            'content_only ' => true,
            'layout' => $this->getLayout(),
            'stylesheets' => $this->getStylesheets(),
            'javascript' => $this->getJavascript(),
            'js_custom_vars' => Media::getJsDef(),
            'notifications' => $this->prepareNotifications(),
            'HOOK_HEADER' => false
        ];

        $data = array_merge($data, $templateVars);
        $this->context->smarty->assign($data);
        $this->context->smarty->escape_html = false;
        die($this->context->smarty->fetch('module:wirecardpaymentgateway/views/templates/front/creditcard_ui.tpl'));
    }

    /**
     * Create order
     *
     * @param Cart   $cart
     * @param string $paymentMethod
     * @return int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    private function createOrder($cart, $paymentMethod)
    {
        $orderManager = new OrderManager($this->module);

        $order = new Order($orderManager->createOrder(
            $cart,
            OrderManager::WIRECARD_OS_STARTING,
            $paymentMethod
        ));

        return $order->id;
    }

    /**
     * Recover failed order
     *
     * @param $orderId
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    private function processFailure($orderId)
    {
        $order = new Order($orderId);
        if ($order->current_state == Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            $order->setCurrentState(_PS_OS_ERROR_);
            $params = [
                'submitReorder' => true,
                'id_order' => (int)$orderId
            ];
            $this->redirectWithNotifications(
                $this->context->link->getPageLink('order', true, $order->id_lang, $params)
            );
        }
    }
}
