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

namespace WirecardEE\Prestashop\Classes\Engine;

use Wirecard\PaymentSdk\BackendService;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\Payment;

/**
 * Class PaymentSdkResponse
 *
 * @package WirecardEE\Prestashop\Classes\Engine
 * @since 2.1.0
 */
abstract class PaymentSdkResponse implements ProcessableEngineResponse
{
    /** @var BackendService */
    protected $backend_service;

    /** @var Payment */
    protected $payment;

    /**
     * @param array|string $response
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function process($response)
    {
        $config = $this->getPaymentConfig(
            \Tools::getValue('payment_type')
        );
        $this->backend_service = new BackendService($config, new WirecardLogger());
    }

    /**
     * @param string $payment_type
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 2.1.0
     */
    private function getPaymentConfig($payment_type)
    {
        $shop_config_service = new ShopConfigurationService($payment_type);
        return (new PaymentConfigurationFactory($shop_config_service))->createConfig();
    }
}
