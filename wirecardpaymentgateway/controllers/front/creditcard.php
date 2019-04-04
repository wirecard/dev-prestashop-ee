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

    public function initContent()
    {
        $this->ajax = true;
        $this->vaultModel = new CreditCardVault($this->context->customer->id);
        parent::initContent();
    }

    public function postProcess() {
        $payload = \Tools::getAllValues();
        $paymentMethod = \Tools::getValue('payment_method');
        $url = $this->context->link->getModuleLink(
            $this->module->name,
            'return',
            array(
                'payment_type' => $paymentMethod,
                'payment_state' => 'success',
            )
        );
        $payment = $this->module->getPaymentFromType($paymentMethod);
        $config = $payment->createPaymentConfig($this->module);
        $transactionService = new TransactionService($config, new WirecardLogger());
        $response = $transactionService->processJsResponse($payload, $url);
        return $response->getRedirectUrl();
    }

    /**
     * list user credit cards from the vault
     *
     * @since 1.1.0
     */
    public function displayAjaxListStoredCards()
    {
        header('Content-Type: application/json; charset=utf8');
        die(json_encode($this->vaultModel->getUserCards()));
    }

    /**
     * add a card and return a list of stored user credit cards
     *
     * @since 1.1.0
     */
    public function displayAjaxAddCard()
    {
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
    public function displayAjaxDeleteCard()
    {
        $ccid = Tools::getValue('ccid');

        if (!$ccid) {
            $this->displayAjaxListStoredCards();
        }

        $this->vaultModel->deleteCard($ccid);

        $this->displayAjaxListStoredCards();
    }
}
