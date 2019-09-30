<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace PrestaShop\PrestaShop\Core\Payment;

class PaymentOption
{
    public function setCallToActionText($string)
    {
        return $this;
    }

    public function setAction($string)
    {
        return;
    }

    public function setLogo($string)
    {
        return;
    }

    public function setAdditionalInformation($string)
    {
        return;
    }

    public function setModuleName($string) {
        return $this;
    }

    public function setForm($string) {
        return;
    }

    public function getCallToActionText()
    {
        return "callToActionText";
    }

    public function getAction()
    {
        return "action";
    }

    public function getLogo()
    {
        return "Logo";
    }

    public function getAdditionalInformation()
    {
        return "additionalInformation";
    }

    public function getModuleName() {
        return \WirecardPaymentGateway::NAME;
    }

    public function getForm() {
        return "form";
    }
}
