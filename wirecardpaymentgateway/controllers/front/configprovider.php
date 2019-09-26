<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Models\PaymentCreditCard;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @property WirecardPaymentGateway module
 *
 * @since 1.0.0
 */
class WirecardPaymentGatewayConfigProviderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->ajax = true;
    }

    /**
     * Generate Credit Card config
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
}
