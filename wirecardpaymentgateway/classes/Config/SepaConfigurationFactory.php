<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config;

use Wirecard\PaymentSdk\Config\SepaConfig;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

/**
 * Class SepaConfigurationFactory
 *
 * @package WirecardEE\Prestashop\Classes\Config
 * @since 2.1.0
 */
class SepaConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * @var ShopConfigurationService
     * @since 2.1.0
     */
    protected $configService;

    /**
     * SepaConfigurationFactory constructor.
     *
     * @param ShopConfigurationService $configService
     * @since 2.1.0
     */
    public function __construct(ShopConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Builds up a SEPA-specific config
     *
     * @return SepaConfig
     * @since 2.1.0
     */
    public function createConfig()
    {
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
