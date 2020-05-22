<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use DateTime;
use Wirecard\PaymentSdk\Constant\ChallengeInd;
use WirecardEE\Prestashop\Models\CreditCardVault;

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
     * @var array
     * @since 2.3.0
     */
    private $validOrderStates;

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
        $this->customer         = $customer;
        $this->currentOrder     = $orderId;
        $this->challengeInd     = $challengeInd;
        $this->tokenId          = $tokenId;
        $this->validOrderStates = [
            (int)\Configuration::get('PS_OS_PAYMENT'),
            (int)\Configuration::get('PS_OS_PREPARATION'),
            (int)\Configuration::get('PS_OS_CANCELED'),
            (int)\Configuration::get('PS_OS_REFUND'),
            (int)\Configuration::get(OrderManager::WIRECARD_OS_AUTHORIZATION),
        ];
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
     * Get Customers date of password change
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
        $customer_id = (int)$this->customer->id;
        $pastDate = $pastDate->format(DateTime::ISO8601);

        $sql = "SELECT COUNT(*) AS count
                FROM ps_orders
                WHERE valid=1 AND current_state IN (".implode(',', $this->validOrderStates).")
                              AND date_add >= '$pastDate' AND date_add < NOW() AND id_customer=$customer_id";

        $res = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (!$res) {
            return 0;
        }

        return $res['count'];
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
            $date = new DateTime($time);
        } catch (\Exception $e) {
            $date = null;
        }
        return $date;
    }
}
