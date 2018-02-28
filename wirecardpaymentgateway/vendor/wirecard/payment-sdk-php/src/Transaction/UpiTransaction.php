<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Transaction;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Exception\UnsupportedOperationException;

/**
 * Class UpiTransaction
 * @package Wirecard\PaymentSdk\Transaction
 */
class UpiTransaction extends Transaction implements Reservable
{
    const NAME = 'unionpayinternational';

    /**
     * @var string
     */
    private $tokenId;

    /**
     * @var string
     */
    private $termUrl;

    /**
     * @var PaymentMethodConfig
     */
    private $config;

    /**
     * @param PaymentMethodConfig $config
     * @return UpiTransaction
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $tokenId
     */
    public function setTokenId($tokenId)
    {
        $this->tokenId = $tokenId;
    }

    /**
     * @return string
     */
    public function getTermUrl()
    {
        return $this->termUrl;
    }

    /**
     * @param string $termUrl
     * @return $this
     */
    public function setTermUrl($termUrl)
    {
        $this->termUrl = $termUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT_PAYMENTS;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return array
     */
    protected function mappedSpecificProperties()
    {
        $this->validate();
        $result = ['payment-methods' => ['payment-method' => [[
            'name' => CreditCardTransaction::NAME
        ]]]];

        if (null !== $this->tokenId) {
            $result['card-token'] = [
                'token-id' => $this->tokenId,
            ];
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForReserve()
    {
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_REFERENCED_AUTHORIZATION;
                break;
            default:
                $transactionType = self::TYPE_AUTHORIZATION;
        }

        return $transactionType;
    }

    /**
     * @return string
     */
    protected function retrieveTransactionTypeForPay()
    {
        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
                $transactionType = self::TYPE_CAPTURE_AUTHORIZATION;
                break;
            case self::TYPE_PURCHASE:
                $transactionType = self::TYPE_REFERENCED_PURCHASE;
                break;
            default:
                $transactionType = self::TYPE_PURCHASE;
        }

        return $transactionType;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForCancel()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        switch ($this->parentTransactionType) {
            case self::TYPE_AUTHORIZATION:
            case self::TYPE_REFERENCED_AUTHORIZATION:
                $transactionType = self::TYPE_VOID_AUTHORIZATION;
                break;
            case self::TYPE_REFUND_CAPTURE:
            case self::TYPE_REFUND_PURCHASE:
                $transactionType = 'void-' . $this->parentTransactionType;
                break;
            case self::TYPE_PURCHASE:
            case self::TYPE_REFERENCED_PURCHASE:
                $transactionType = self::TYPE_VOID_PURCHASE;
                break;
            case self::TYPE_CAPTURE_AUTHORIZATION:
                $transactionType = self::TYPE_VOID_CAPTURE;
                break;
            default:
                throw new UnsupportedOperationException('The transaction can not be canceled.');
        }

        return $transactionType;
    }

    /**
     * @throws MandatoryFieldMissingException|UnsupportedOperationException
     * @return string
     */
    protected function retrieveTransactionTypeForRefund()
    {
        if (!$this->parentTransactionId) {
            throw new MandatoryFieldMissingException('No transaction for cancellation set.');
        }

        switch ($this->parentTransactionType) {
            case $this::TYPE_PURCHASE:
            case $this::TYPE_REFERENCED_PURCHASE:
                return 'refund-purchase';
            case $this::TYPE_CAPTURE_AUTHORIZATION:
                return 'refund-capture';
            default:
                throw new UnsupportedOperationException('The transaction can not be refunded.');
        }
    }

    /**
     * @return string
     */
    public function retrieveOperationType()
    {
        return ($this->operation === Operation::RESERVE) ? self::TYPE_AUTHORIZATION : self::TYPE_PURCHASE;
    }

    /**
     *
     * @throws \Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException
     */
    protected function validate()
    {
        if ($this->tokenId === null && $this->parentTransactionId === null) {
            throw new MandatoryFieldMissingException(
                'At least one of these two parameters has to be provided: token ID, parent transaction ID.'
            );
        }
    }
}
