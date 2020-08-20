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

use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Class CurrencyHelper
 *
 * @since 2.0.0
 */
class CurrencyHelper
{
    /** @var \Currency[] */
    protected $currencies = [];

    /**
     * CurrencyConverter constructor.
     * @since 2.0.0
     */
    public function __construct()
    {
        $availableCurrencies = \Currency::getCurrencies();

        foreach ($availableCurrencies as $currency) {
            $this->currencies[$currency["iso_code"]] = $currency["conversion_rate"];
        }
    }

    /**
     * Convert the given amount to a different currency based on the
     * exchange rate of the shop system.
     *
     * @param float $amount
     * @param string $currency
     * @return float|int
     * @since 2.0.0
     */
    public function convertToCurrency($amount, $currency)
    {
        return isset($this->currencies[$currency])
            ? (float)$amount * (float)$this->currencies[$currency]
            : (float)$amount;
    }

    /**
     * Create a paymentSDK Amount
     *
     * @param float|int $amount
     * @param string $currency
     * @return Amount
     * @since 2.0.0
     */
    public function getAmount($amount, $currency)
    {
        return new Amount(
            \Tools::ps_round($amount, _PS_PRICE_COMPUTE_PRECISION_),
            $currency
        );
    }

    /**
     * Create and convert a paymentSDK Amount
     *
     * @param float $amount
     * @param string $currency
     * @return Amount
     * @since 2.0.0
     */
    public function getConvertedAmount($amount, $currency)
    {
        return new Amount(
            \Tools::ps_round(
                $this->convertToCurrency($amount, $currency),
                _PS_PRICE_COMPUTE_PRECISION_
            ),
            $currency
        );
    }
}
