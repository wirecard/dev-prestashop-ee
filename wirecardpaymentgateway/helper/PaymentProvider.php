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

use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use WirecardEE\Prestashop\Models\Payment;
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

/**
 * Class PaymentProvider
 *
 * Note that this class suppresses the coupling warning as it pulls in
 * a large number of dependencies by necessity. The class itself is however not
 * complex.
 *
 * @package WirecardEE\Prestashop\Helper
 * @since 2.4.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentProvider
{
    /**
     * Get all available payment models
     *
     * @return array
     * @since 2.4.0
     */
    public static function getPayments()
    {
        return array(
            PaymentCreditCard::TYPE => new PaymentCreditCard(),
            PaymentPaypal::TYPE => new PaymentPaypal(),
            PaymentSepaDirectDebit::TYPE => new PaymentSepaDirectDebit(),
            PaymentSepaCreditTransfer::TYPE => new PaymentSepaCreditTransfer(),
            PaymentSofort::TYPE => new PaymentSofort(),
            PaymentIdeal::TYPE => new PaymentIdeal(),
            RatepayInvoiceTransaction::PAYMENT_NAME => new PaymentGuaranteedInvoiceRatepay(),
            PaymentPtwentyfour::TYPE => new PaymentPtwentyfour(),
            PaymentPoiPia::TYPE => new PaymentPoiPia(),
            PaymentAlipayCrossborder::TYPE => new PaymentAlipayCrossborder()
        );
    }

    /**
     * Get a specific payment model by type
     *
     * @param string $type
     * @return Payment
     * @since 2.4.0
     */
    public static function getPayment($type)
    {
        $payments = self::getPayments();

        if (!array_key_exists($type, $payments)) {
            throw new \UnexpectedValueException("Payment '$type' does not exist.");
        }

        return $payments[$type];
    }
}
