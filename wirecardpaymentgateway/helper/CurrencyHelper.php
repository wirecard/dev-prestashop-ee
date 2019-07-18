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
