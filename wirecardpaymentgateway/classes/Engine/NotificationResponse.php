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

namespace WirecardEE\Prestashop\Classes\Engine;

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class NotificationResponse
 *
 * @since 2.1.0
 *@package WirecardEE\Prestashop\Classes\Engine
 */
final class NotificationResponse extends PaymentSdkResponse
{
    /**
     * @param string $response
     * @return SuccessResponse|FailureResponse
     * @since 2.1.0
     */
    public function process($response)
    {
        parent::process($response);

        return $this->backend_service->handleNotification($response);
    }
}
