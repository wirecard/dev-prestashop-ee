<?php

namespace WirecardEE\Prestashop\Classes\Config\Factories;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use WirecardEE\Prestashop\Classes\Config\Interfaces\ConfigurationFactoryInterface;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;

/**
 * Class GenericConfigurationFactory
 *
 * @package WirecardEE\Prestashop\Classes\Config\Factories
 * @since 2.1.0
 */
class GenericConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * @var ShopConfigurationService
     * @since 2.1.0
     */
    protected $configService;

    /**
     * GenericConfigurationFactory constructor.
     *
     * @param ShopConfigurationService $configService
     * @since 2.1.0
     */
    public function __construct(ShopConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Makes a generic payment method config for PayPal, Sofort, etc
     *
     * @return PaymentMethodConfig
     * @since 2.1.0
     */
    public function createConfig()
    {
        return new PaymentMethodConfig(
            $this->configService->getType(),
            $this->configService->getField('merchant_account_id'),
            $this->configService->getField('secret')
        );
    }
}
