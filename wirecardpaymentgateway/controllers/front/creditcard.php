<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use \WirecardEE\Prestashop\Models\CreditCardVault;
use \WirecardEE\Prestashop\Helper\TranslationHelper;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @var CreditCardVault $vaultModel
     */
    private $vaultModel;

    public function initContent()
    {
        $this->ajax = true;
        $this->vaultModel = new CreditCardVault($this->context->customer->id);
        parent::initContent();
    }

    /**
     * list user credit cards from the vault
     *
     * @since 1.1.0
     */
    public function displayAjaxListStoredCards()
    {
        $data = [
            'cards' => $this->vaultModel->getUserCards($this->context->cart->id_address_invoice),
            'strings' => [
                'use' => $this->getTranslatedString('vault_use_card_text'),
                'delete' => $this->getTranslatedString('vault_delete_card_text')
            ]
        ];

        $response = new JsonResponse($data);
        $response->send();
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

        $this->vaultModel->addCard($maskedpan, $tokenId, $this->context->cart->id_address_invoice);

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
