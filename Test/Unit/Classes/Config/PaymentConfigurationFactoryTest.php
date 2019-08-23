<?php

use WirecardEE\Prestashop\Classes\Config\Factories\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Classes\Config\Services\ShopConfigurationService;
use WirecardEE\Prestashop\Models\PaymentSofort;
use WirecardEE\Prestashop\Models\PaymentCreditCard;
use WirecardEE\Prestashop\Models\PaymentSepaDirectDebit;

class PaymentConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsAValidConfiguration()
    {
        $shopConfigService = new ShopConfigurationService(PaymentSofort::TYPE);
        $configFactory = new PaymentConfigurationFactory($shopConfigService);

        $sdkConfig = $configFactory->createConfig();

        $this->assertInstanceOf(
            \Wirecard\PaymentSdk\Config\Config::class,
            $sdkConfig
        );
    }

    public function testItCreatesACreditCardConfiguration()
    {
        $shopConfigService = new ShopConfigurationService(PaymentCreditCard::TYPE);
        $configFactory = new PaymentConfigurationFactory($shopConfigService);

        $sdkConfig = $configFactory->createConfig();
        $creditCardConfig = $sdkConfig->get(PaymentCreditCard::TYPE);

        $this->assertInstanceOf(
            \Wirecard\PaymentSdk\Config\CreditCardConfig::class,
            $creditCardConfig
        );
    }

    public function testItCreatesASepaConfiguration()
    {
        $shopConfigService = new ShopConfigurationService(PaymentSepaDirectDebit::TYPE);
        $configFactory = new PaymentConfigurationFactory($shopConfigService);

        $sdkConfig = $configFactory->createConfig();
        $sepaConfig = $sdkConfig->get(PaymentSepaDirectDebit::TYPE);

        $this->assertInstanceOf(
            \Wirecard\PaymentSdk\Config\SepaConfig::class,
            $sepaConfig
        );
    }

    public function testItCreatesAGenericConfiguration()
    {
        $shopConfigService = new ShopConfigurationService(PaymentSofort::TYPE);
        $configFactory = new PaymentConfigurationFactory($shopConfigService);

        $sdkConfig = $configFactory->createConfig();
        $paymentMethodConfig = $sdkConfig->get(PaymentSofort::TYPE);

        $this->assertEquals(
            $paymentMethodConfig->getPaymentMethodName(),
            PaymentSofort::TYPE
        );

        $this->assertInstanceOf(
            \Wirecard\PaymentSdk\Config\PaymentMethodConfig::class,
            $paymentMethodConfig
        );
    }
}
