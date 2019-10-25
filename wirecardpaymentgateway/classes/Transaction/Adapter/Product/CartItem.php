<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Adapter\Product;

use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Class CartItem
 * @package WirecardEE\Prestashop\Classes\Transaction\Adapter\Product
 * @since 2.4.0
 */
class CartItem implements CartItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $shortDescription;

    /**
     * @var string
     */
    private $productReference;

    /**
     * @var Amount
     */
    private $taxAmount;

    /**
     * @var float
     */
    private $taxRate;

    /**
     * ProductData constructor.
     * @param string $name
     * @param Amount $amount
     * @param int $quantity
     * @param string $shortDescription
     * @param string $productReference
     * @param null|Amount $taxAmount
     * @param null|float $taxRate
     * @since 2.4.0
     */
    public function __construct(
        $name,
        $amount,
        $quantity,
        $shortDescription,
        $productReference,
        $taxAmount = null,
        $taxRate = null
    ) {
        $this->name = $name;
        $this->amount = $amount;
        $this->quantity = $quantity;
        $this->shortDescription = $shortDescription;
        $this->productReference = $productReference;

        if ($taxAmount !== null) {
            $this->taxAmount = $taxAmount;
        }

        if ($taxRate !== null) {
            $this->taxRate = $taxRate;
        }
    }

    /**
     * @return string
     * @since 2.4.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @since 2.4.0
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Amount
     * @since 2.4.0
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     * @since 2.4.0
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     * @since 2.4.0
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @since 2.4.0
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     * @since 2.4.0
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     * @since 2.4.0
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string
     * @since 2.4.0
     */
    public function getProductReference()
    {
        return $this->productReference;
    }

    /**
     * @param string $productReference
     * @since 2.4.0
     */
    public function setProductReference($productReference)
    {
        $this->productReference = $productReference;
    }

    /**
     * @return Amount
     * @since 2.4.0
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param Amount $taxAmount
     * @since 2.4.0
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
    }

    /**
     * @return float
     * @since 2.4.0
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     * @since 2.4.0
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
    }
}
