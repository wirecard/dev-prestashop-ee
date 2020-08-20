<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\TemplateHelper;

/**
 * Class FormPost
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
final class FormPost implements ProcessablePaymentResponse
{
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
            TemplateHelper::getFrontendTemplatePath('creditcard_submitform'),
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
