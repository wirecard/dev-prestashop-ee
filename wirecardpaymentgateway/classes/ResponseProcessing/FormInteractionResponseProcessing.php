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
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace WirecardEE\Prestashop\Classes\ResponseProcessing;

use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use WirecardEE\Prestashop\Helper\Service\ContextService;

/**
 * Class FormInteractionResponseProcessing
 * @package WirecardEE\Prestashop\Classes\ResponseProcessing
 * @since 2.1.0
 */
final class FormInteractionResponseProcessing implements ResponseProcessing
{

    /** @var FormInteractionResponse  */
    private $response;

    /** @var string */
    private $template_path;

    /**
     * FormInteractionResponseProcessing constructor.
     *
     * @param FormInteractionResponse $response
     */
    public function __construct($response)
    {
        $this->response = $response;
        $this->template_path = join(
            DIRECTORY_SEPARATOR,
            [_PS_MODULE_DIR_ . 'wirecardpaymentgateway', 'views', 'templates', 'front', 'creditcard_submitform.tpl']
        );
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        $context_service = new ContextService(\Context::getContext());

        $context_service->showTemplateWithData(
            $this->template_path,
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
