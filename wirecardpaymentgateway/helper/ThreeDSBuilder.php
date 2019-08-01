<?php

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Transaction\Transaction;

class ThreeDSBuilder
{

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * ThreeDSBuilder constructor.
     * @param Transaction $transaction
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     *
     */
    public function buildThreeDSFields()
    {

        return $this->transaction;
    }

}
