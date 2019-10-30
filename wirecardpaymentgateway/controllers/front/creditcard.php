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
    use TranslationHelper;

    /**
     * @var string
     * @since 2.3.0
     */
    const TRANSLATION_FILE = "creditcard";

    /**
     * @var CreditCardVault $credit_card_vault
     */
    private $credit_card_vault;

    public function initContent()
    {
        $this->ajax = true;
        $this->credit_card_vault = new CreditCardVault($this->context->customer->id);

        parent::initContent();
    }

    /**
     * list user credit cards from the vault
     *
     * @since 1.1.0
     */
    public function displayAjaxListStoredCards()
    {
        $templatePath = TemplateHelper::getTemplatePath('creditcard_list');

        $this->context->smarty->assign($this->getCardListData());
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
        $tokenId = Tools::getValue('tokenId');
        $maskedPan = Tools::getValue('maskedPan');

        if (!$tokenId || !$maskedPan) {
            $response = new Response("No token or PAN provided.", 400);
            $response->send();

            return;
        }

        $this->credit_card_vault->addCard($maskedPan, $tokenId, $this->context->cart->id_address_invoice);

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
        $cardId = Tools::getValue('cardId');

        if (!$cardId) {
            $this->displayAjaxListStoredCards();
        }

        $this->credit_card_vault->deleteCard($cardId);

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
        $cartId = Tools::getValue('cartId');
        $payment = new PaymentCreditCard();

        try {
            $requestData = $payment->getRequestData($this->module, $this->context, $cartId);
            $response = JsonResponse::fromJsonString($requestData);
        } catch (\Exception $exception) {
            $response = new JsonResponse(null);
        }

        $response->send();
    }

    /**
     * Gets necessary data for building the saved card list.
     *
     * @return array
     * @since 2.4.0
     */
    private function getCardListData()
    {
        return [
            'cards' => $this->credit_card_vault->getUserCards($this->context->cart->id_address_invoice),
            'strings' => [
                'use' => $this->getTranslatedString('vault_use_card_text'),
                'delete' => $this->getTranslatedString('vault_delete_card_text')
            ]
        ];
    }
}
