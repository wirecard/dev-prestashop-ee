<?php

namespace WirecardEE\Prestashop\Helper;

/**
 * Class TransactionManager
 * @since 2.4.0
 */
class TransactionManager
{
    /**
     * @var string
     * @since 2.4.0
     */
    const TRANSACTION_TABLE = 'wirecard_payment_gateway_tx';

    /**
     * @var \Db
     * @since 2.4.0
     */
    protected $database;

    public function __construct()
    {
        $this->database = \Db::getInstance();
    }

    public function markTransactionClosed($transaction_id)
    {
        $whereClause = sprintf(
            'tx_id = "%s"',
            pSQL($transaction_id)
        );

        $this->database->update(
            self::TRANSACTION_TABLE,
            [ 'transaction_state' => 'closed' ],
            $whereClause
        );
    }

    public function getShopTransactionId($transaction_id)
    {
        $whereClause = sprintf(
            'transaction_id = "%s"',
            pSQL($transaction_id)
        );

        $query = (new \DbQuery())
            ->from(self::TRANSACTION_TABLE)
            ->where($whereClause);

        $transaction = $this->database->getRow($query);

        return $transaction->tx_id;
    }
}