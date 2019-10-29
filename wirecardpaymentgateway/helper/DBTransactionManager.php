<?php

namespace WirecardEE\Prestashop\Helper;

/**
 * Class DBTransactionManager
 * @since 2.4.0
 */
class DBTransactionManager
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

    /**
     * @param string $transaction_id
     * @since 2.4.0
     */
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
}
