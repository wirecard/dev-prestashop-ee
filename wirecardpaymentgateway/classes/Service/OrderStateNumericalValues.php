<?php


namespace WirecardEE\Prestashop\Classes\Service;


/**
 * Class OrderStateNumericalValues hold the numerical values needed to calculate the next order state
 * @package WirecardEE\Prestashop\Classes\Service
 *
 *
 */
class OrderStateNumericalValues
{
    /**
     * @var float
     */
    private $orderOpenAmount;

    /**
     * OrderStateNumericalValues constructor.
     * @param float $orderOpenAmount
     */
    public function __construct($orderOpenAmount)
    {
        $this->orderOpenAmount = $orderOpenAmount;
    }

    /**
     * @return float
     */
    public function getOrderOpenAmount()
    {
        return $this->orderOpenAmount;
    }

}