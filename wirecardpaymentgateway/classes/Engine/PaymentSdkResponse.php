<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
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
