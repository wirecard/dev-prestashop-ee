<?php

namespace WirecardEE\Prestashop\Helper;

use WirecardEE\Prestashop\Models\PaymentAlipayCrossborder;
use WirecardEE\Prestashop\Models\PaymentCreditCard;
use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Models\PaymentIdeal;
use WirecardEE\Prestashop\Models\PaymentMasterpass;
use WirecardEE\Prestashop\Models\PaymentPaypal;
use WirecardEE\Prestashop\Models\PaymentPoiPia;
use WirecardEE\Prestashop\Models\PaymentPtwentyfour;
use WirecardEE\Prestashop\Models\PaymentSepaCreditTransfer;
use WirecardEE\Prestashop\Models\PaymentSepaDirectDebit;
use WirecardEE\Prestashop\Models\PaymentSofort;

class PaymentProvider {
    public static function getPayments()
    {
        return array(
            PaymentCreditCard::TYPE => new PaymentCreditCard(),
            PaymentPaypal::TYPE => new PaymentPaypal(),
            PaymentSepaDirectDebit::TYPE => new PaymentSepaDirectDebit(),
            PaymentSepaCreditTransfer::TYPE => new PaymentSepaCreditTransfer(),
            PaymentSofort::TYPE => new PaymentSofort(),
            PaymentIdeal::TYPE => new PaymentIdeal(),
            PaymentGuaranteedInvoiceRatepay::TYPE => new PaymentGuaranteedInvoiceRatepay(),
            PaymentPtwentyfour::TYPE => new PaymentPtwentyfour(),
            PaymentPoiPia::TYPE => new PaymentPoiPia(),
            PaymentMasterpass::TYPE => new PaymentMasterpass(),
            PaymentAlipayCrossborder::TYPE => new PaymentAlipayCrossborder()
        );
    }

    public static function getPayment($type)
    {
        $payments = self::getPayments();

        if (!array_key_exists($type, $payments)) {
            throw new \UnexpectedValueException("Payment '$type' does not exist.");
        }

        return $payments[$type];
    }
}