<?php

namespace WirecardEE\Prestashop\Helper;

use DateTime;
use Order;
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
        $states = [\OrderState::FLAG_PAID, \OrderState::FLAG_SHIPPED, \OrderState::FLAG_DELIVERY, \OrderState::FLAG_LOGABLE];
        $dateBeforeSixMonths = $this->convertToDateTime("-6 months");
        return $this->countOrders($dateBeforeSixMonths, $states);
    }

    /**
     * Count Orders.
     * @param DateTime $pastDate
     * @param array $states
     * @return int
     *
     * @since 2.2.0
     */
    private function countOrders($pastDate, $states)
    {
        $count = 0;
        $orders = Order::getCustomerOrders($this->customer->id);
        foreach ($orders as $order) {
            $orderDate = $this->convertToDateTime($order['date_add']);
            if (($orderDate > $pastDate) && (in_array($order['id_order_state'], $states))) {
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

//    public function isReorderedItems(){
//
//    }
}
