<?php

namespace WirecardEE\Prestashop\Classes\Config\Interfaces;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Interface ConfigurationFactoryInterface
 *
 * @package WirecardEE\Prestashop\Classes\Config\Interfaces
 * @since 2.1.0
 */
interface ConfigurationFactoryInterface {
    /**
     * This method should take all necessary steps to return a fully fledged PaymentMethodConfig
     *
     * @return PaymentMethodConfig
     * @since 2.1.0
     */
    public function createConfig();
}