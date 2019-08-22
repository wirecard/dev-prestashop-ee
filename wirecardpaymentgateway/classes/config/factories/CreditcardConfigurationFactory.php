<?php

namespace WirecardEE\Prestashop\Classes\Config\Factories;

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use WirecardEE\Prestashop\Helper\CurrencyHelper;
use WirecardEE\Prestashop\Classes\Config\Interfaces\ConfigurationFactoryInterface;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;

/**
 * Class CreditcardConfigurationFactory
 *
 * @package WirecardEE\Prestashop\Classes\Config\Factories
 * @since 2.1.0
 */
class CreditcardConfigurationFactory implements ConfigurationFactoryInterface {
    /**
     * @var ShopConfigurationService
     * @since 2.1.0
     */
    protected $configService;

    /**
     * CreditcardConfigurationFactory constructor.
     *
     * @param ShopConfigurationService $configService
     * @since 2.1.0
     */
    public function __construct(ShopConfigurationService $configService) {
        $this->configService = $configService;
    }

    /**
     * Makes a credit card-specific config
     *
     * @return CreditCardConfig
     * @since 2.1.0
     */
    public function createConfig() {
        $currency = \Context::getContext()->currency;
        $currencyConverter = new CurrencyHelper();

        $paymentConfig = $paymentMethodConfig = new CreditCardConfig(
            $this->configService->getField('merchant_account_id'),
            $this->configService->getField('secret')
        );

        if ($this->configService->getField('three_d_merchant_account_id') !== '') {
            $paymentConfig->setThreeDCredentials(
                $this->configService->getField('three_d_merchant_account_id'),
                $this->configService->getField('three_d_secret')
            );
        }

        if (is_numeric($this->configService->getField('ssl_max_limit'))
            && $this->configService->getField('ssl_max_limit') >= 0) {
            $paymentConfig->addSslMaxLimit(
                $currencyConverter->getConvertedAmount(
                    $this->configService->getField('ssl_max_limit'),
                    $currency->iso_code
                )
            );
        }

        if (is_numeric($this->configService->getField('three_d_min_limit'))
            && $this->configService->getField('three_d_min_limit') >= 0) {
            $paymentConfig->addThreeDMinLimit(
                $currencyConverter->getConvertedAmount(
                    $this->configService->getField('three_d_min_limit'),
                    $currency->iso_code
                )
            );
        }

        return $paymentMethodConfig;
    }
}