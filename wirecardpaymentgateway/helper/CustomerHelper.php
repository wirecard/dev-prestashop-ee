<?php

namespace WirecardEE\Prestashop\Helper;

use DateTime;
use Order;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use WirecardEE\Prestashop\Models\CreditCardVault;
use Wirecard\PaymentSdk\Constant\ChallengeInd;

/**
 * Class CustomerHelper
 * @package WirecardEE\Prestashop\Helper
 *
 * @since 2.2.0
 */
class CustomerHelper
{
    /**
     * @var \Customer
     * @since 2.2.0
     */
    private $customer;

    /**
     * @var \Order
     * @since 2.2.0
     */
    private $currentOrder;

    /**
     * @var string
     * @since 2.2.0
     */
    private $challengeInd;

    /**
     * @var string|null
     * @since 2.2.0
     */
    private $tokenId;

    /**
     * CustomerHelper constructor.
     * @param \Customer $customer
     * @param int $orderId
     * @param string $challengeInd
     * @param string|null $tokenId
     *
     * @since 2.2.0
     */
    public function __construct($customer, $orderId, $challengeInd, $tokenId)
    {
        $this->customer      = $customer;
        $this->currentOrder = $orderId;
        $this->challengeInd = $challengeInd;
        $this->tokenId      = $tokenId;
    }

    /**
     * Get Challenge indicator
     * @return string
     *
     * @since 2.2.0
     */
    public function getChallengeIndicator()
    {
        if (is_null($this->tokenId)) {
            return $this->challengeInd;
        }
        $vault = new CreditCardVault($this->customer->id);
        if ($vault->getCard($this->tokenId)) {
            return $this->challengeInd;
        }
        return ChallengeInd::CHALLENGE_MANDATE;
    }

    /**
     * Get Customers date of account creation
     * @return DateTime
     *
     * @since 2.2.0
     */
    public function getAccountCreationDate()
    {
        return $this->convertToDateTime($this->customer->date_add);
    }

    /**
     * Get Customers date of last login
     * @return string
     *
     * @since 2.2.0
     */
    public function getAccountLastLogin()
    {
        $stats = $this->customer->getStats();
        return gmdate('Y-m-d\TH:i:s\Z', strtotime($stats['last_visit']));
    }

    /**
     * Get Customers date of account update
     * @return DateTime
     *
     * @since 2.2.0
     */
    public function getAccountUpdateDate()
    {
        return $this->convertToDateTime($this->customer->date_upd);
    }

    /**
     * Get Customers date of account update
     * @return DateTime
     *
     * @since 2.2.0
     */
    public function getAccountPassChangeDate()
    {
        return $this->convertToDateTime($this->customer->last_passwd_gen);
    }

    /**
     * Get customers date of first shipping address use
     * @param int $shippingAddressId
     * @return DateTime
     *
     * @since 2.2.0
     */
    public function getShippingAddressFirstUse($shippingAddressId)
    {
        $address = new \Address($shippingAddressId);
        return $this->convertToDateTime($address->date_add);
    }

    /**
     * Get successful orders from last six months
     * @return int
     *
     * @since 2.2.0
     */
    public function getSuccessfulOrdersLastSixMonths()
    {
        $dateBeforeSixMonths = $this->convertToDateTime("-6 months");
        return $this->countOrders($dateBeforeSixMonths);
    }

    /**
     * Count only valid Orders fom specific date.
     * @param DateTime $pastDate
     * @return int
     *
     * @since 2.2.0
     */
    private function countOrders($pastDate)
    {
        $count = 0;
        $orders = Order::getCustomerOrders($this->customer->id);
        foreach ($orders as $order) {
            $orderDate = $this->convertToDateTime($order['date_add']);
            if (($orderDate > $pastDate) && $order['valid']) {
                $count++;
            }
        }
        return $count;
    }


    /**
     * Get Customers date of card creation
     * @return DateTime
     *
     * @since 2.2.0
     */
    public function getCardCreationDate()
    {
        $vault = new CreditCardVault($this->customer->id);
        $card = $vault->getCard($this->tokenId);
        if (isset($card)) {
            return $this->convertToDateTime($card["date_add"]);
        }
        return $this->convertToDateTime('now');
    }

    /**
     * Convert to DateTime
     * @param string $time
     * @return null|DateTime
     *
     * @since 2.2.0
     */
    private function convertToDateTime($time)
    {
        try {
            $dateAdd = new DateTime($time);
        } catch (\Exception $e) {
            $dateAdd = null;
        }
        return $dateAdd;
    }

    /**
     * Check if one item in
     * @param \Cart $cart
     * @return string
     *
     * @since 2.2.0
     */
    public function isReorderedItems($cart)
    {
        // All orders from customer
        $orders = Order::getCustomerOrders($cart->id_customer);
        $cartProducts = $cart->getProducts();
        $cartProductIds = array();
        /* @var \Product $product */
        foreach ($cartProducts as $product) {
            $cartProductIds[] = $product['id_product'];
        }
        /* @var Order $order */
        foreach ($orders as $order) {
            $orderClass= new \Order($order['id_order']);
            $orderProducts = $orderClass->getProducts();
            foreach ($orderProducts as $orderProduct) {
                if (in_array($orderProduct['id_product'], $cartProductIds)) {
                    return RiskInfoReorder::REORDERED;
                }
            }
        }
        return RiskInfoReorder::FIRST_TIME_ORDERED;
    }
}
