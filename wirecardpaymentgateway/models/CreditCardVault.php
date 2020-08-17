<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use WirecardEE\Prestashop\Helper\AddressHashHelper;
use WirecardEE\Prestashop\Helper\Logger;

/**
 * Basic CreditCard vault class
 *
 * used for managing the stored credit card tokens
 *
 * Class CreditCardVault
 *
 * @since 1.1.0
 */
class CreditCardVault
{
    private $table = 'wirecard_payment_gateway_cc';

    private $userId;

    /** @var Logger  */
    private $logger;

    /** @var \Db */
    private $database;

    /** @var AddressHashHelper */
    private $addressHashHelper;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->logger = new Logger();

        $this->database = \Db::getInstance();
        $this->addressHashHelper = new AddressHashHelper();
    }

    /**
     * get all cards by user_id and order them by increment id
     *
     * @param int $addressId
     * @return array|false|\mysqli_result|null|\PDOStatement|resource
     *
     * @since 1.1.0
     */
    public function getUserCardsByAddressId($addressId)
    {
        $addressHash = $this->addressHashHelper->getHashFromAddressId($addressId);
        $query = new \DbQuery();
        $query->from($this->table)
            ->where('user_id = ' . (int)$this->userId)
            ->where('address_hash = "' . $addressHash . '"');
        $query->orderBy('cc_id');
        try {
            return $this->database->executeS($query);
        } catch (\PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * add a card token in
     *
     * @param $maskedPan
     * @param $token
     * @param $addressId
     *
     * @return int|string
     *
     * @since 1.1.0
     */
    public function addCard($maskedPan, $token, $addressId)
    {
        $userCards = $this->getUserCardsByAddressId($addressId);
        foreach ($userCards as $userCard) {
            if ($userCard['token'] === $token) {
                $this->updateCardLastUsed($token);
                return $userCard['cc_id'];
            }
        }

        try {
            $this->database->insert($this->table, array(
                'masked_pan' => pSQL($maskedPan),
                'token' => pSQL($token),
                'user_id' => (int)$this->userId,
                'address_id' => (int)$addressId,
                'date_add' => date('Y-m-d H:i:s'),
                'date_last_used' => date('Y-m-d H:i:s'),
                'address_hash' => $this->addressHashHelper->getHashFromAddressId($addressId)
            ));
        } catch (\PrestaShopDatabaseException $e) {
            $this->logger->error(__METHOD__ . $e->getMessage());
            return 0;
        }

        if ($this->database->getNumberError() > 0) {
            $this->logger->error(__METHOD__ . $this->database->getMsgError());
            return 0;
        }

        return $this->database->Insert_ID();
    }

    /**
     * get a card by its token id
     *
     * @param $token
     * @return array|bool|null|object
     *
     * @since 1.1.0
     */
    public function getCard($token)
    {
        $query = new \DbQuery();
        $query->from($this->table)->where('token = "' . pSQL($token) . '"');

        return $this->database->getRow($query);
    }

    /**
     * delete a card by card id and
     *
     * @param $id
     * @return bool
     *
     * @since 1.1.0
     */
    public function deleteCard($id)
    {
        return $this->database->delete($this->table, 'cc_id = ' . (int)$id . ' AND user_id = ' . (int)$this->userId);
    }

    /**
     * Update card last used date
     * @param string $token
     * @return bool
     *
     * @since 2.2.0
     */
    public function updateCardLastUsed($token)
    {
        return $this->database->update($this->table, ['date_last_used' => date('Y-m-d H:i:s')], 'token=' . $token);
    }
}
