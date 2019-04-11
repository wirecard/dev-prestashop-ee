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

namespace WirecardEE\Prestashop\Models;

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

    private $logger;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->logger = new Logger();
    }

    /**
     * get all cards by user_id and order them by increment id
     *
     * @return array|false|\mysqli_result|null|\PDOStatement|resource
     *
     * @since 1.1.0
     */
    public function getUserCards()
    {
        $query = new \DbQuery();
        $query->from($this->table)->where('user_id = ' . (int)$this->userId);
        $query->orderBy('last_used DESC,cc_id DESC');

        try {
            return \Db::getInstance()->executeS($query);
        } catch (\PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * add a card token in
     *
     * @param $maskedPan
     * @param $token
     * @return int|string
     *
     * @since 1.1.0
     */
    public function addCard($maskedPan, $token)
    {
        $db = \Db::getInstance();

        $existing = $this->getCard($token);

        if ($existing) {
            return $existing["cc_id"];
        }

        try {
            $db->insert($this->table, array(
                'masked_pan' => pSQL($maskedPan),
                'token' => pSQL($token),
                'user_id' => (int)$this->userId
            ));
        } catch (\PrestaShopDatabaseException $e) {
            $this->logger->error(__METHOD__ . $e->getMessage());
            return 0;
        }

        if ($db->getNumberError() > 0) {
            $this->logger->error(__METHOD__ . $db->getMsgError());
            return 0;
        }

        return $db->Insert_ID();
    }

    /**
     * @param $tokenId
     * @return int
     */
    public function updateLastUsed($tokenId){
        $db = \Db::getInstance();
        try {
            $where = 'token = "' . pSQL($tokenId) . '"';
            $db->update($this->table, [
                'last_used' => [
                    'value' => 'CURRENT_TIMESTAMP',
                    'type' => 'sql'
                ],
            ], $where);
        } catch (\PrestaShopDatabaseException $e) {
            $this->logger->error(__METHOD__ . $e->getMessage());
            return 0;
        }

        if ($db->getNumberError() > 0) {
            $this->logger->error(__METHOD__ . $db->getMsgError());
            return 0;
        }
        return $db->Affected_Rows();
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

        return \Db::getInstance()->getRow($query);
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
        $db = \Db::getInstance();

        return $db->delete($this->table, 'cc_id = ' . (int)$id . ' AND user_id = ' . (int)$this->userId);
    }
}
