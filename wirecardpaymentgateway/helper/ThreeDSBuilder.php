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
     * @return Transaction|\WirecardEE\Prestashop\Models\Transaction
     * @since 2.2.0
     */
    public function getThreeDsTransaction()
    {

        $shipping_account = $this->getShippingAccount();
        $account_holder   = $this->getCardHolderAccount();
        $account_info     = $this->getAccountInfo();
        $risk_info        = $this->getRiskInfo();

//        $account_holder->setAccountInfo( $account_info );
//        $account_holder->setCrmId( $this->get_merchant_crm_id() );
//
//        $this->transaction->setAccountHolder( $account_holder );
//        $this->transaction->setShipping( $shipping_account );
//        $this->transaction->setRiskInfo( $risk_info );
//        $this->transaction->setIsoTransactionType( IsoTransactionType::GOODS_SERVICE_PURCHASE );

        return $this->transaction;
    }

    /**
     * @since 2.2.0
     */
    private function getShippingAccount()
    {
    }

    /**
     *
     */
    private function getCardHolderAccount()
    {
    }

    /**
     * @since 2.2.0
     */
    private function getAccountInfo()
    {
    }

    /**
     * @since 2.2.0
     */
    private function getRiskInfo()
    {
    }
}
