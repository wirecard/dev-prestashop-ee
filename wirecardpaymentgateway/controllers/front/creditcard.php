<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use WirecardEE\Prestashop\Helper\TemplateHelper;
use WirecardEE\Prestashop\Models\CreditCardVault;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

/**
 * @property WirecardPaymentGateway module
 *
 * @since 1.1.0
 */
class WirecardPaymentGatewayCreditCardModuleFrontController extends ModuleFrontController
{
    /**
     * @var CreditCardVault $credit_card_vault_model
     */
    private $credit_card_vault_model;

    public function initContent()
    {
        $this->ajax = true;
        $this->credit_card_vault_model = new CreditCardVault($this->context->customer->id);

        parent::initContent();
    }

    /**
     * list user credit cards from the vault
     *
     * @since 1.1.0
     */
    public function displayAjaxListStoredCards()
    {
        $templatePath = TemplateHelper::getFrontendTemplatePath('creditcard_list');
        $cards = $this->credit_card_vault_model->getUserCards($this->context->cart->id_address_invoice);

        $this->context->smarty->assign([ 'cards' => $cards ]);
        $html = $this->context->smarty->fetch($templatePath);

        $response = new JsonResponse([ 'html' => $html ]);
        $response->send();
    }

    /**
     * Save a card token in the credit card vault
     *
     * @since 2.4.0 Use proper Symfony response
     * @since 1.1.0
     */
    public function displayAjaxSaveCard()
    {
        $token_id = Tools::getValue('token_id');
        $masked_pan = Tools::getValue('masked_pan');

        if (!$token_id || !$masked_pan) {
            $response = new Response("No token or PAN provided.", 400);
            $response->send();

            return;
        }

        $this->credit_card_vault_model->addCard($masked_pan, $token_id, $this->context->cart->id_address_invoice);

        $response = new Response("", 201);
        $response->send();
    }

    /**
     * Delete a card and return a list of stored user credit cards
     *
     * @since 1.1.0
     */
    public function displayAjaxDeleteCard()
    {
        $card_id = Tools::getValue('card_id');

        if (!$card_id) {
            $this->displayAjaxListStoredCards();
        }

        $this->credit_card_vault_model->deleteCard($card_id);

        $this->displayAjaxListStoredCards();
    }


    /**
     * Generate Credit Card config
     *
     * @since 2.4.0 Move function to Credit Card controller
     * @since 1.0.0
     */
    public function displayAjaxGetSeamlessConfig()
    {
        $cart_id = Tools::getValue('cart_id');
        $payment = new PaymentCreditCard();

        try {
            $request_data = $payment->getRequestData($this->context, $cart_id);
            $response = JsonResponse::fromJsonString($request_data);
        } catch (\Exception $exception) {
            $response = new JsonResponse(null);
        }

        $response->send();
    }

	/**
	 * Add proper error message on credit card failed payment
	 *
	 * @since 2.7.0
	 */
	public function displayAjaxCreditCardFailure()
	{
		session_start();
		$errorList = Tools::getValue('errors');
		$notification = json_encode([
			'error' => $errorList
		]);
		$_SESSION['notifications'] = $notification;
	}
}
