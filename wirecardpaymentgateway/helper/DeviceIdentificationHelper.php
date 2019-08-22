<?php

namespace WirecardEE\Prestashop\Helper;

use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;

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
