<?php

namespace WirecardEE\Prestashop\Helper;

use Customer;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Entity\AccountInfo;
use Wirecard\PaymentSdk\Entity\RiskInfo;
use Wirecard\PaymentSdk\Constant\IsoTransactionType;

class ThreeDSBuilder
{

    /*
     * @var AdditionalInformationBuilder
     */
    private $additionalInformationBuilder;

    /**
     * @var CustomerHelper $customerHelper
     */
    private $customerHelper;

    /**
     * ThreeDSBuilder constructor.
     */
    public function __construct()
    {
        $this->additionalInformationBuilder = new AdditionalInformationBuilder();
    }

    /**
     * @var \Cart $cart
     * @var int $orderId
     * @var Transaction $transaction
     * @var string $challengeInd
     * @return Transaction|\WirecardEE\Prestashop\Models\Transaction
     * @since 2.2.0
     */
    public function getThreeDsTransaction($cart, $orderId, $transaction, $challengeInd)
    {
        $customer = new Customer($cart->id_customer);
        $tokenId = \Tools::getValue('token_id');
        $this->customerHelper = new CustomerHelper($customer, $orderId, $challengeInd, $tokenId);

        $accountHolder = $this->additionalInformationBuilder->createCreditCardAccountHolder(
            $cart,
            $customer->firstname,
            $customer->lastname
        );
        $accountInfo = $this->getAccountInfo($customer, $cart);
        $accountHolder->setAccountInfo($accountInfo);
        $transaction->setAccountHolder($accountHolder);

//        $accountHolder->setCrmId( $this->get_merchant_crm_id() );
//        $risk_info        = $this->getRiskInfo();
//        $this->transaction->setRiskInfo( $risk_info );
//        $this->transaction->setIsoTransactionType( IsoTransactionType::GOODS_SERVICE_PURCHASE );

        return $transaction;
    }

    /**
     * @param \PrestaShop\PrestaShop\Adapter\Entity\Customer $customer
     * @param \Cart $cart
     * @return AccountInfo
     * @since 2.2.0
     */
    private function getAccountInfo($customer, $cart)
    {
        $accountInfo = new AccountInfo();
        // Add specific AccountInfo data for authenticated user
        $accountInfo->setAuthMethod(AuthMethod::GUEST_CHECKOUT);
        if (!$customer->isGuest()) {
            $accountInfo->setAuthMethod(AuthMethod::USER_CHECKOUT);
            $accountInfo->setAuthTimestamp($this->customerHelper->getAccountLastLogin());
            $accountInfo->setChallengeInd($this->customerHelper->getChallengeIndicator());
            $accountInfo->setCreationDate($this->customerHelper->getAccountCreationDate());
            $accountInfo->setUpdateDate($this->customerHelper->getAccountUpdateDate());
            $accountInfo->setPassChangeDate($this->customerHelper->getAccountPassChangeDate());
            $accountInfo->setShippingAddressFirstUse(
                $this->customerHelper->getShippingAddressFirstUse($cart->id_address_delivery)
            );
            //$accountInfo->setCardCreationDate( $this->customerHelper->getCardCreationDate() );
            $accountInfo->setAmountPurchasesLastSixMonths(
                $this->customerHelper->getSuccessfulOrdersLastSixMonths()
            );
        }
        return $accountInfo;
    }


//    /**
//     * @since 2.2.0
//     */
//    private function getRiskInfo()
//    {
//    }



}
