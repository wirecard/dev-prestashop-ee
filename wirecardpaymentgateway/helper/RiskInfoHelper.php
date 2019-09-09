<?php

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Entity\RiskInfo;

/**
 * Class RiskInfoHelper
 * @package WirecardEE\Prestashop\Helper
 * @since 2.2.0
 */
class RiskInfoHelper
{
    const STOCK_MANAGEMENT = 'PS_STOCK_MANAGEMENT';

    /**
     * @var \Customer
     * @since 2.2.0
     */
    protected $customer;

    /**
     * @var \Cart
     * @since 2.2.0
     */
    protected $cart;

    /**
     * @var bool
     * @since 2.2.0
     */
    protected $hasStockManagement;

    /**
     * RiskInfoHelper constructor.
     *
     * @param $customer
     * @param $cart
     * @since 2.2.0
     */
    public function __construct($customer, $cart)
    {
        $this->customer = $customer;
        $this->cart = $cart;
        $this->hasStockManagement = \Configuration::get(self::STOCK_MANAGEMENT);
    }

    /**
     * Builds and returns all relevant risk information
     *
     * @return RiskInfo
     * @since 2.2.0
     */
    public function buildRiskInfo()
    {
        $riskInfo = new RiskInfo();
        $cartHelper = new CartHelper($this->cart);
        $riskInfo->setDeliveryEmailAddress($this->customer->email);
        if (!$this->customer->isGuest()) {
            $riskInfo->setReorderItems($cartHelper->isReorderedItems());
        }
        $riskInfo->setAvailability($cartHelper->checkAvailability());
        if (!$this->hasStockManagement) {
            $riskInfo->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
        }

        return $riskInfo;
    }
}
