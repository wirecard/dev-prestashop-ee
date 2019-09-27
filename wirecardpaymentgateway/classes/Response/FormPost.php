<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use WirecardEE\Prestashop\Helper\Service\ContextService;

/**
 * Class FormPost
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
final class FormPost implements ProcessablePaymentResponse
{
    const FORM_TEMPLATE = _PS_MODULE_DIR_ . 'wirecardpaymentgateway' . DIRECTORY_SEPARATOR .
    'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR .
    'creditcard_submitform.tpl';

    /** @var FormInteractionResponse  */
    private $response;

    /**
     * FormInteractionResponseProcessing constructor.
     *
     * @param FormInteractionResponse $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        $context_service = new ContextService(\Context::getContext());

        $context_service->showTemplateWithData(
            self::FORM_TEMPLATE,
            $this->getDataFromResponse($this->response)
        );
    }

    /**
     * @param FormInteractionResponse $response
     * @return array
     * @since 2.1.0
     */
    private function getDataFromResponse($response)
    {
        return [
            'url' => $response->getUrl(),
            'method' => $response->getMethod(),
            'form_fields' => $response->getFormFields()
        ];
    }
}
