<?php

namespace WirecardEE\Prestashop\Classes\Config\Factories;

use Wirecard\PaymentSdk\Config\SepaConfig;
use WirecardEE\Prestashop\Classes\Config\Interfaces\ConfigurationFactoryInterface;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;

class SepaConfigurationFactory implements ConfigurationFactoryInterface {
    /**
     * @var ShopConfigurationService
     * @since 2.1.0
     */
    protected $configService;

    public function __construct(ShopConfigurationService $configService) {
        $this->configService = $configService;
    }

    public function createConfig() {
        $paymentConfig = $paymentMethodConfig = new SepaConfig(
            $this->configService->getType(),
            $this->configService->getField('merchant_account_id'),
            $this->configService->getField('secret')
        );

        $paymentConfig->setCreditorId(
            $this->configService->getField('creditor_id')
        );

        return $paymentMethodConfig;
    }
}