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
 */

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Response\InteractionResponse;

/**
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
        $cartId = $cart->id;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 ||
            !$this->module->active
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $paymentType = Tools::getValue('paymentType');
        /** @var Payment $payment */
        $payment = $this->module->getPaymentFromType($paymentType);
        if ($payment) {
            $config = $payment->createConfig();
            $amount = new Amount(2, 'EUR');
            $operation = Configuration::get(WirecardPaymentGateway::buildParamName($paymentType, 'payment_action'));

            /** @var Transaction $transaction */
            $transaction = $payment->createTransaction();
            $transaction->setNotificationUrl('test');
            $transaction->setRedirect('test');
            $transaction->setAmount($amount);

            $customFields = new CustomFieldCollection();
            $customFields->add(new CustomField('orderId', $cartId));
            $transaction->setCustomFields($customFields);

            if (Configuration::get(WirecardPaymentGateway::buildParamName($paymentType, 'shopping_basket'))) {
                //TODO: Create shoppingbasket here
            }

            if (Configuration::get(WirecardPaymentGateway::buildParamName($paymentType, 'descriptor'))) {
                //TODO: Create descriptor
                $transaction->setDescriptor();
            }

            if (Configuration::get(WirecardPaymentGateway::buildParamName($paymentType, 'send_additional'))) {
                //TODO: create additional information for fps
            }

            return $this->executeTransaction($transaction, $config, $operation);
        }
    }

    /**
     * Execute reserve and pay operations
     *
     * @param $transaction
     * @param $config
     * @param $operation
     * @return string
     * @since 1.0.0
     */
    public function executeTransaction($transaction, $config, $operation)
    {
        $transactionService = new TransactionService($config);
        try {
            /** @var \Wirecard\PaymentSdk\Response\Response $response */
            $response = $transactionService->process($transaction, $operation);
        } catch (Exception $exception) {
            //throw exceptions in prestashop
        }

        if ($response instanceof InteractionResponse) {
            $redirect = $response->getRedirectUrl();
        }

        return $redirect;
    }
}
