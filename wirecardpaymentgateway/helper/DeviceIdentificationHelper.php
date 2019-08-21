<?php

namespace WirecardEE\Prestashop\Helper;

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
        $paymentConfiguration = new PaymentConfiguration(PaymentGuaranteedInvoiceRatepay::TYPE);

        $timestamp = microtime();
        $customerId = $paymentConfiguration->getField('merachant_account_id');
        $deviceIdentToken = md5($customerId . "_" . $timestamp);

        return $deviceIdentToken;
    }
}
