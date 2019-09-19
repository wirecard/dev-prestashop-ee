<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

/**
 * Class GenericConfigurationFactory
 *
 * @package WirecardEE\Prestashop\Classes\Config
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
