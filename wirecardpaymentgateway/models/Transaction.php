<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Models;

use Wirecard\PaymentSdk\Response\Response;

/**
 * Basic Transaction class
 *
 * Class Transaction
 *
 * @since 1.0.0
 */
class Transaction extends \ObjectModel
{
    public $tx_id;

    public $transaction_id;

    public $parent_transaction_id;

    public $order_id;

    public $cart_id;

    public $ordernumber;

    public $paymentmethod;

    public $transaction_state;

    public $amount;

    public $currency;

    public $response;

    public $transaction_type;

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
     * Create transaction in wirecard_payment_gateway_tx
     *
     * @param int $idOrder
     * @param int $idCart
     * @param float $amount
     * @param string $currency
     * @param string $transactionState
     * @param string $orderNumber
     * @param Response $response
     * @return mixed
     * @since 1.0.0
     */
    public static function create(
        $idOrder,
        $idCart,
        $amount,
        $currency,
        $response,
        $transactionState,
        $orderNumber = null
    ) {
        $db = \Db::getInstance();
        $parentTransactionId = '';

        if ((new Transaction)->get($response->getParentTransactionId())) {
            $parentTransactionId = $response->getParentTransactionId();
        }

        $db->insert('wirecard_payment_gateway_tx', array(
            'transaction_id' => pSQL($response->getTransactionId()),
            'parent_transaction_id' => pSQL($parentTransactionId),
            'order_id' => $idOrder === null ? 'NULL' : (int)$idOrder,
            'ordernumber' => $orderNumber === null ? 'NULL' : pSQL($orderNumber),
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
     * Get single transaction per transaction id
     *
     * @param string $transactionId
     * @return mixed
     * @since 1.0.0
     */
    public function get($transactionId)
    {
        $query = new \DbQuery();
        $query->from('wirecard_payment_gateway_tx')->where('transaction_id = ' . (int)$transactionId);

        return \Db::getInstance()->getRow($query);
    }
}
