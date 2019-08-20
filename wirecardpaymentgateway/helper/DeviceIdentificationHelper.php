<?php

namespace WirecardEE\Prestashop\Helper;

use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;

class DeviceIdentificationHelper {
    public static function generateFingerprint()
    {
        $paymentConfiguration = new PaymentConfiguration(PaymentGuaranteedInvoiceRatepay::TYPE);

        $timestamp = microtime();
        $customerId = $paymentConfiguration->getField('merachant_account_id');
        $deviceIdentToken = md5($customerId . "_" . $timestamp);

        return $deviceIdentToken;
    }
}