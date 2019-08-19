<?php

namespace WirecardEE\Prestashop\Helper;

use Credit_Card_Vault;
use Customer;
use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Entity\AccountInfo;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\RiskInfo;
use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use WirecardEE\Prestashop\Models\CreditCardVault;

class ThreeDSBuilder
{

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var Transaction $transaction
     * @return Transaction|\WirecardEE\Prestashop\Models\Transaction
     * @since 2.2.0
     */
    public function getThreeDsTransaction($transaction)
    {
        $accountHolder = $transaction->getAccountHolder();
        $accountInfo   = $this->getAccountInfo();

        $accountHolder->setAccountInfo($accountInfo);
        $this->transaction->setAccountHolder($accountHolder);

//        $accountHolder->setCrmId( $this->get_merchant_crm_id() );
//        $risk_info        = $this->getRiskInfo();
//        $this->transaction->setRiskInfo( $risk_info );
//        $this->transaction->setIsoTransactionType( IsoTransactionType::GOODS_SERVICE_PURCHASE );

        return $this->transaction;
    }


    /**
     * @since 2.2.0
     */
    private function getAccountInfo()
    {
        $accountInfo = new AccountInfo();
        // Add specific AccountInfo data for authenticated user
        $accountInfo->setAuthMethod(AuthMethod::GUEST_CHECKOUT);
        if (Customer::isLogged()) {
            $accountInfo->setAuthMethod(AuthMethod::USER_CHECKOUT);
//            $accountInfo->setChallengeInd( $this->user_data_helper->get_challenge_indicator() );
//            $accountInfo->setCreationDate( $this->user_data_helper->get_account_creation_date() );
//            $accountInfo->setUpdateDate( $this->user_data_helper->get_account_update_date() );
//            $accountInfo->setShippingAddressFirstUse( $this->user_data_helper->get_shipping_address_first_use() );
//            $accountInfo->setCardCreationDate( $this->user_data_helper->get_card_creation_date() );
//            $accountInfo->setAmountPurchasesLastSixMonths( $this->user_data_helper->get_successful_orders_last_six_months() );
        }

        return $accountInfo;
    }


//    /**
//     * @since 2.2.0
//     */
//    private function getRiskInfo()
//    {
//    }


//    /**
//     * Get challenge indicator depending on existing token
//     *   04  - for new one-click-checkout token
//     *   predefined indicator from settings for existing token and non-one-click-checkout
//     *
//     * @return string
//     * @since 2.2.0
//     */
//    public function getChallengeIndicator() {
//        if ( is_null( $this->token_id ) ) {
//            return $this->challenge_ind;
//        }
//
//        $vault = new CreditCardVault();
//        if ( $vault->getCard( $this->token_id ) ) {
//            return $this->challenge_ind;
//        }
//
//        return ChallengeInd::CHALLENGE_MANDATE;
//    }
}
