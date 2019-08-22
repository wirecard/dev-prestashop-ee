<?php

namespace WirecardEE\Prestashop\Classes\Config\Factories;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use WirecardEE\Prestashop\Classes\Config\Interfaces\ConfigurationFactoryInterface;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;

class PaymentConfigurationFactory {

    /**
     * @var ShopConfigurationService
     * @since 2.1.0
     */
    protected $configService;

    public function __construct(ShopConfigurationService $configService) {
        $this->configService = $configService;
    }

    public function createConfig() {
        $factoryType = $this->resolveFactoryType();

        /** @var ConfigurationFactoryInterface $configFactory */
        $configFactory = new $factoryType($this->configService);

        $sdkConfiguration = new Config(
            $this->configService->getField('base_url'),
            $this->configService->getField('http_user'),
            $this->configService->getField('http_pass')
        );

        $sdkConfiguration->setShopInfo(\WirecardPaymentGateway::SHOP_NAME, _PS_VERSION_);
        $sdkConfiguration->setPluginInfo(\WirecardPaymentGateway::EXTENSION_HEADER_PLUGIN_NAME, \WirecardPaymentGateway::VERSION);
        $sdkConfiguration->add(
            $configFactory->createConfig()
        );

        return $sdkConfiguration;
    }

    private function resolveFactoryType() {
        switch ($this->configService->getType()) {
            case CreditCardTransaction::NAME:
                return CreditcardConfigurationFactory::class;
            case SepaCreditTransferTransaction::NAME:
            case SepaDirectDebitTransaction::NAME:
                return SepaConfigurationFactory::class;
            default:
                return GenericConfigurationFactory::class;
        }
    }
}