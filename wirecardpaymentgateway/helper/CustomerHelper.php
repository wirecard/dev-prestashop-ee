<?php

namespace WirecardEE\Prestashop\Helper;

use DateTime;
use Order;
use WirecardEE\Prestashop\Models\CreditCardVault;
use Wirecard\PaymentSdk\Constant\ChallengeInd;

class CustomerHelper
{
    const UNLIMITED = -1;

    /**
     * @var \Customer
     */
    private $customer;

    /**
     * @var \Order
     */
    private $currentOrder;

    /**
     * @var string
     */
    private $challengeInd;

    /**
     * @var string|null
     */
    private $tokenId;

    /**
     * @var \Address $address
     */
    private $address;

    /**
     * CustomerHelper constructor.
     * @param \Customer $customer
     * @param int $orderId
     * @param string $challengeInd
     * @param string|null $tokenId
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
     * @return string
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
     * @since 2.2.0
     */
    public function getAccountCreationDate()
    {
        return $this->convertToDateTime($this->customer->date_add);
    }

    /**
     * Get Customers date of last login
     * @return string
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
     * @since 2.2.0
     */
    public function getAccountUpdateDate()
    {
        return $this->convertToDateTime($this->customer->date_upd);
    }

    /**
     * Get Customers date of account update
     * @return DateTime
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
     * @since 2.2.0
     */
    public function getSuccessfulOrdersLastSixMonths()
    {
        $orders = Order::getCustomerOrders($this->customer->id);
        $dateBeforeSixMonths = $this->convertToDateTime("-6 months");
        $count = 0;
        foreach ($orders as $order) {
            $orderDate = $this->convertToDateTime($order['date_add']);
            if (($orderDate > $dateBeforeSixMonths) && ($order['id_order_state'] === \OrderState::FLAG_PAID)) {
                $count++;
            }
        }
        return $count;
    }

//    /**
//     * Get Customers date of card creation
//     * @return DateTime
//     * @since 2.2.0
//     */
//    public function getCardCreationDate() {
//        $vault = new CreditCardVault($this->customer->id);
//        $card = $vault->getCard( $this->tokenId );
//        if (isset($card)) {
//            return $this->convertToDateTime($card["date_add"]);
//        }
//        return $this->convertToDateTime('now');
//    }

    /**
     * Convert to DateTime
     * @param string $time
     * @return null|DateTime
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
}
