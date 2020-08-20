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

namespace WirecardEE\Prestashop\Helper;

use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;

/**
 * Class DeviceIdentificationHelper
 *
 * @package WirecardEE\Prestashop\Helper
 * @since 2.1.0
 */
class DeviceIdentificationHelper
{
    /**
     * Generate a device fingerprint for Guaranteed Invoice By Wirecard
     *
     * @since 2.1.0
     * @return string
     */
    public static function generateFingerprint()
    {
        $shopConfigService = new ShopConfigurationService(PaymentGuaranteedInvoiceRatepay::TYPE);

        $timestamp = microtime();
        $customerId = $shopConfigService->getField('merachant_account_id');
        $deviceIdentToken = md5($customerId . "_" . $timestamp);

        return $deviceIdentToken;
    }
}
