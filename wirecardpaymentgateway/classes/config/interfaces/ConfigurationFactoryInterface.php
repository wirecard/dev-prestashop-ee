<?php

namespace WirecardEE\Prestashop\Classes\Config\Interfaces;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

interface ConfigurationFactoryInterface {
    /**
     * @return PaymentMethodConfig
     */
    public function createConfig();
}