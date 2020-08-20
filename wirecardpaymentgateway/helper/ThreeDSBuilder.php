<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use Wirecard\PaymentSdk\Entity\AccountInfo;
use Wirecard\PaymentSdk\Entity\RiskInfo;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class ThreeDSBuilder
 * @package WirecardEE\Prestashop\Helper
 */
class ThreeDSBuilder
{

    /**
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
        $tokenId = \Tools::getValue('token_id');
        $customer = new \Customer($cart->id_customer);
        $this->customerHelper = new CustomerHelper($customer, $orderId, $challengeInd, $tokenId);

        $accountHolder = $this->additionalInformationBuilder->createAccountHolder(
            $cart,
            $customer->firstname,
            $customer->lastname
        );

        $shipping = $this->additionalInformationBuilder->createAccountHolder(
            $cart,
            'shipping',
            $customer->firstname,
            $customer->lastname
        );
        $shipping->setPhone(null);

        $accountInfo = $this->getAccountInfo($customer, $cart, $challengeInd);
        $accountHolder->setAccountInfo($accountInfo);
        $crmId = $this->getMerchantCrmId($customer);
        $accountHolder->setCrmId($crmId);
        $transaction->setAccountHolder($accountHolder);
        $transaction->setShipping($shipping);

        $riskInfo = $this->getRiskInfo($customer, $cart);
        $transaction->setRiskInfo($riskInfo);
        $transaction->setIsoTransactionType(IsoTransactionType::GOODS_SERVICE_PURCHASE);
        return $transaction;
    }

    /**
     * @param \Customer $customer
     * @param \Cart $cart
     * @param $indicator
     * @return AccountInfo
     * @since 2.2.0
     */
    private function getAccountInfo($customer, $cart, $indicator)
    {
        $accountInfo = new AccountInfo();
        $accountInfo->setAuthMethod(AuthMethod::GUEST_CHECKOUT);
        $accountInfo->setAuthTimestamp();
        $accountInfo->setChallengeInd($indicator);
        if (!$customer->isGuest()) {
            // Add specific AccountInfo data for authenticated user
            $accountInfo->setAuthMethod(AuthMethod::USER_CHECKOUT);
            $accountInfo->setAuthTimestamp($this->customerHelper->getAccountLastLogin());
            $accountInfo->setCreationDate($this->customerHelper->getAccountCreationDate());
            $accountInfo->setUpdateDate($this->customerHelper->getAccountUpdateDate());
            $accountInfo->setPassChangeDate($this->customerHelper->getAccountPassChangeDate());
            $accountInfo->setShippingAddressFirstUse(
                $this->customerHelper->getShippingAddressFirstUse($cart->id_address_delivery)
            );
            $accountInfo->setCardCreationDate($this->customerHelper->getCardCreationDate());
            $accountInfo->setAmountPurchasesLastSixMonths(
                $this->customerHelper->getSuccessfulOrdersLastSixMonths()
            );
        }
        return $accountInfo;
    }

    /**
     * Get merchant crm id from user id
     * @param \Customer $customer
     * @return null|string
     *
     * @since 2.2.0
     */
    private function getMerchantCrmId($customer)
    {
        if (!$customer->isGuest()) {
            return (string) $customer->id;
        }
        return null;
    }


    /**
     * Get risk info.
     *
     * @param \Customer $customer
     * @param \Cart $cart
     * @return RiskInfo

     * @since 2.2.0
     */
    private function getRiskInfo($customer, $cart)
    {
        $riskInfoHelper = new RiskInfoHelper($customer, $cart);

        return $riskInfoHelper->buildRiskInfo();
    }
}
