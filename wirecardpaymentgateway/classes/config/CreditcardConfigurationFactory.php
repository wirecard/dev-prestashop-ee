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

namespace WirecardEE\Prestashop\Classes\Config;

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use WirecardEE\Prestashop\Helper\CurrencyHelper;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

/**
 * Class CreditcardConfigurationFactory
 *
 * @package WirecardEE\Prestashop\Classes\Config
 * @since 2.1.0
 */
class CreditcardConfigurationFactory implements ConfigurationFactoryInterface
{
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
    public function __construct(ShopConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Makes a credit card-specific config
     *
     * @return CreditCardConfig
     * @since 2.1.0
     */
    public function createConfig()
    {
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
