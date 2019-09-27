<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

/**
 * Class PaymentConfigurationFactory
 *
 * @package WirecardEE\Prestashop\Classes\Config
 * @since 2.1.0
 */
class PaymentConfigurationFactory
{

    /**
     * @var ShopConfigurationService
     * @since 2.1.0
     */
    protected $configService;

    /**
     * PaymentConfigurationFactory constructor.
     *
     * @param ShopConfigurationService $configService
     * @since 2.1.0
     */
    public function __construct(ShopConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Builds up a paymentSdk config containing the specific payment method config
     *
     * @return Config
     * @since 2.1.0
     */
    public function createConfig()
    {
        $factoryType = $this->resolveFactoryType();

        /** @var ConfigurationFactoryInterface $configFactory */
        $configFactory = new $factoryType($this->configService);

        $sdkConfiguration = new Config(
            $this->configService->getField('base_url'),
            $this->configService->getField('http_user'),
            $this->configService->getField('http_pass')
        );

        $sdkConfiguration->setShopInfo(
            \WirecardPaymentGateway::SHOP_NAME,
            _PS_VERSION_
        );

        $sdkConfiguration->setPluginInfo(
            \WirecardPaymentGateway::EXTENSION_HEADER_PLUGIN_NAME,
            \WirecardPaymentGateway::VERSION
        );

        $sdkConfiguration->add(
            $configFactory->createConfig()
        );

        return $sdkConfiguration;
    }

    /**
     * Determines what type of configuration to build
     *
     * @return string
     * @since 2.1.0
     */
    private function resolveFactoryType()
    {
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
