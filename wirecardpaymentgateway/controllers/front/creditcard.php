<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Symfony\Component\HttpFoundation\Response;
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
        $templatePath = join(
            DIRECTORY_SEPARATOR,
            [_PS_MODULE_DIR_, \WirecardPaymentGateway::NAME, 'views', 'templates', 'front', 'creditcard_list.tpl']
        );

        $data = [
            'cards' => $this->vaultModel->getUserCards($this->context->cart->id_address_invoice),
            'strings' => [
                'use' => $this->getTranslatedString('vault_use_card_text'),
                'delete' => $this->getTranslatedString('vault_delete_card_text')
            ]
        ];

        $this->context->smarty->assign($data);
        $html = $this->context->smarty->fetch($templatePath);

        $response = new JsonResponse([ 'html' => $html ]);
        $response->send();
    }

    /**
     * add a card and return a list of stored user credit cards
     *
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

        $this->vaultModel->addCard($maskedPan, $tokenId, $this->context->cart->id_address_invoice);

        $response = new Response("", 201);
        $response->send();
    }

    /**
     * delete a card and return a list of stored user credit cards
     *
     * @since 1.1.0
     */
    public function displayAjaxDeleteCard()
    {
        $cardId = Tools::getValue('cardId');

        if (!$cardId) {
            $this->displayAjaxListStoredCards();
        }

        $this->vaultModel->deleteCard($cardId);

        $this->displayAjaxListStoredCards();
    }
}
