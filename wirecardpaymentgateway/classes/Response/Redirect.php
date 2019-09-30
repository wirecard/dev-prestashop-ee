<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\InteractionResponse;

/**
 * Class Redirect
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
final class Redirect implements ProcessablePaymentResponse
{
    /** @var InteractionResponse  */
    private $response;

    /**
     * InteractionResponseProcessing constructor.
     *
     * @param InteractionResponse $response
     * @since 2.1.0
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
        \Tools::redirect($this->response->getRedirectUrl());
    }
}
