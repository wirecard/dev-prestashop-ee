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
        $accountHolder->setCrmId($this->getMerchantCrmId($customer));
        $transaction->setAccountHolder($accountHolder);

        $riskInfo = $this->getRiskInfo($customer, $cart);
        $transaction->setRiskInfo($riskInfo);
        $transaction->setIsoTransactionType(IsoTransactionType::GOODS_SERVICE_PURCHASE);
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
        $accountInfo->setAuthTimestamp();
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
            $accountInfo->setCardCreationDate($this->customerHelper->getCardCreationDate());
            $accountInfo->setAmountPurchasesLastSixMonths(
                $this->customerHelper->getSuccessfulOrdersLastSixMonths()
            );
        }
        return $accountInfo;
    }

    /**
     * Get merchant crm id from user id
     * @param Customer $customer
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
     * @param \Customer $customer
     * @param \Cart $cart
     * @return RiskInfo
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @since 2.2.0
     */
    private function getRiskInfo($customer, $cart)
    {
        $riskInfo = new RiskInfo();
        $cartHelper = new CartHelper($cart);
        $riskInfo->setDeliveryEmailAddress($customer->email);
        if (!$customer->isGuest()) {
            $riskInfo->setReorderItems($cartHelper->isReorderedItems());
        }
        $riskInfo->setAvailability($cartHelper->checkAvailability());
        return $riskInfo;
    }
}
