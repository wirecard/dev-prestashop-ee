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
}
