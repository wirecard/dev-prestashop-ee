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

/**
 * Basic Transaction class
 *
 * Class Transaction
 *
 * @since 1.0.0
 */
class Transaction extends \ObjectModel
{
    public $txId;

    public $transactionId;

    public $parentTransactionId;

    public $idOrder;

    public $idCart;

    public $orderNumber;

    public $paymentName;

    public $paymentMethod;

    public $paymentNumber;

    public $paymentState;

    public $amount;

    public $currency;

    public $message;

    public $response;

    public $status;

    public $created;

    public $modified;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'wirecard_payment_gateway_tx',
        'primary' => 'tx_id',
        'fields' => array(
            'transaction_id' => array('type' => self::TYPE_STRING),
            'parent_transaction_id' => array('type' => self::TYPE_STRING),
            'order_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'cart_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'ordernumber' => array('type' => self::TYPE_STRING),
            'paymentmethod' => array('type' => self::TYPE_STRING, 'required' => true),
            'transaction_type' => array('type' => self::TYPE_STRING, 'required' => true),
            'transaction_state' => array('type' => self::TYPE_STRING, 'required' => true),
            'amount' => array('type' => self::TYPE_FLOAT, 'required' => true),
            'currency' => array('type' => self::TYPE_STRING, 'required' => true),
            'response' => array('type' => self::TYPE_STRING),
            'created' => array('type' => self::TYPE_DATE, 'required' => true),
            'modified' => array('type' => self::TYPE_DATE),
        ),
    );

    /**
     * @param $id_order
     * @param $id_cart
     * @param $amount
     * @param $currency
     * @param $paymentname
     * @param $paymentmethod
     *
     * @return int
     * @throws PrestaShopDatabaseException
     */
    public static function create($idOrder, $idCart, $amount, $currency, $response)
    {
        //TODO: Implement logic for parent transaction id and closed transactions
        $transactionState = 'success';

        $db = \Db::getInstance();

        $db->insert('wirecard_payment_gateway_tx', array(
            'transaction_id' => $response->getTransactionId(),
            'parent_transaction_id' => $response->getParentTransactionId(),
            'order_id' => $idOrder === null ? 'NULL' : (int)$idOrder,
            'cart_id' => (int)$idCart,
            'paymentmethod' => pSQL($response->getPaymentMethod()),
            'transaction_state' => pSQL($transactionState),
            'transaction_type' => pSQL($response->getTransactionType()),
            'amount' => (float)$amount,
            'currency' => pSQL($currency),
            'response' => pSQL(json_encode($response->getData())),
            'created' => 'NOW()'
        ));

        if ($db->getNumberError() > 0) {
            throw new \PrestaShopDatabaseException($db->getMsgError());
        }

        return $db->Insert_ID();
    }

    /**
     * get transaction from database
     * @param $transactionId
     *
     * @return array|bool|null|object
     */
    public function get($transactionId)
    {
        $query = new \DbQuery();
        $query->from('wirecard_payment_gateway_tx')->where('transaction_id = ' . (int)$transactionId);

        return \Db::getInstance()->getRow($query);
    }
}