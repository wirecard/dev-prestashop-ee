<?php


namespace WirecardEE\Prestashop\Classes\Transaction\PaymentMethod\PostProcessing;


use Wirecard\PaymentSdk\Transaction\Transaction;

class MandatoryDataBuilderFactory
{
    /** @var Transaction */
    private $transaction;

    public function __construct($transaction)
    {
        /** @var Transaction transaction */
        $this->transaction = $transaction;
    }

    public function create()
    {
        //return depending on transaction instance.
        return new CreditCardMandatoryDataBuilder($this->transaction);
    }
}