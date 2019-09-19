<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Models\PaymentCreditCard;

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
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function displayAjaxGetSeamlessConfig()
    {
        $cartId = Tools::getValue('cartId');
        $payment = new PaymentCreditCard();

        try {
            $requestData = $payment->getRequestData($this->module, $this->context, $cartId);

            header('Content-Type: application/json; charset=utf8');
            echo $requestData;
            exit();
        } catch (\Exception $exception) {
            die(Tools::jsonEncode(null));
        }
    }
}
