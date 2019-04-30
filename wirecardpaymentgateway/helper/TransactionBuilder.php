<?php

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class TransactionBuilder
{
    /** @var \WirecardPaymentGateway */
    private $module;

    /** @var \Context */
    private $context;

    /** @var string */
    private $paymentType;

    /** @var \Cart */
    private $cart;

    /** @var \Currency */
    private $currency;

    /** @var CustomFieldCollection */
    private $customFields;

    /** @var AdditionalInformationBuilder */
    private $additionalInformationBuilder;

    /** @var string */
    private $orderId;

    /** @var Transaction */
    private $transaction;

    /**
     * TransactionBuilder constructor.
     * @param $module
     * @param $context
     * @param $cartId
     * @param $paymentType
     * @since 1.4.0
     */
    public function __construct($module, $context, $cartId, $paymentType)
    {
        $this->module = $module;
        $this->context = $context;
        $this->paymentType = $paymentType;
        $this->cart = new \Cart((int) $cartId);
        $this->currency = new \Currency($this->cart->id_currency);
        $this->customFields = new CustomFieldCollection();
        $this->additionalInformationBuilder = new AdditionalInformationBuilder();
    }

    /**
     * Allows setting the order ID in case a pre-existing order is available.
     *
     * @param $orderId
     * @since 1.4.0
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Constructs the requested transaction for the payment type
     *
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     * @throws \Exception
     * @since 1.4.0
     */
    public function buildTransaction()
    {
        if (!isset($this->orderId)) {
            throw new \Exception("An order needs to be created before building a transaction");
        }

        $payment = $this->module->getPaymentFromType($this->paymentType);

        /** @var Transaction $transaction */
        $this->transaction = $payment->createTransaction($this->module, $this->cart, \Tools::getAllValues(), $this->orderId);

        $this->addAmount();
        $this->addRedirects();
        $this->addCustomField('orderId', $this->orderId);
        $this->addTokenId();
        $this->addBasket();
        $this->addDescriptor();
        $this->addAdditionalInformation();

        return $this->transaction;
    }

    /**
     * Add the payment amount to the transaction
     *
     * @since 1.4.0
     */
    private function addAmount() {
        $amount = round($this->cart->getOrderTotal(), 2);
        $this->transaction->setAmount(new Amount((float)$amount, $this->currency->iso_code));
    }

    /**
     * Add the necessary redirects to the transaction
     *
     * @since 1.4.0
     */
    private function addRedirects() {
        $cartId = $this->cart->id;

        $redirectUrls = new Redirect(
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'success'),
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'cancel'),
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'failure')
        );

        $this->transaction->setNotificationUrl($this->module->createNotificationUrl($cartId, $this->paymentType));
        $this->transaction->setRedirect($redirectUrls);
    }

    /**
     * Add custom field to transaction
     *
     * @param $key
     * @param $value
     * @since 1.4.0
     */
    private function addCustomField($key, $value) {
        $this->customFields->add(new CustomField($key, $value));
        $this->transaction->setCustomFields($this->customFields);
    }

    /**
     * Set the token ID if required
     *
     * @since 1.4.0
     */
    private function addTokenId() {
        if ( $this->transaction instanceof CreditCardTransaction && \Tools::getValue('token_id')) {
            $this->transaction->setTokenId(\Tools::getValue('token_id'));
        }
    }

    /**
     * Add the basket if required
     *
     * @since 1.4.0
     */
    private function addBasket() {
        if ($this->module->getConfigValue($this->paymentType, 'shopping_basket')) {
            $this->transaction->setBasket(
                $this->additionalInformationBuilder->createBasket(
                    $this->cart,
                    $this->transaction,
                    $this->currency->iso_code
                )
            );
        }
    }

    /**
     * Add the descriptor if required
     *
     * @since 1.4.0
     */
    private function addDescriptor() {
        if ($this->module->getConfigValue($this->paymentType, 'descriptor')) {
            $this->transaction->setDescriptor($this->additionalInformationBuilder->createDescriptor($this->orderId));
        }
    }

    /**
     * Add additional information if required
     *
     * @since 1.4.0
     */
    private function addAdditionalInformation() {
        if ($this->module->getConfigValue($this->paymentType, 'send_additional')) {
            $firstName = null;
            $lastName = null;

            if (\Tools::getValue('last_name')) {
                $lastName = \Tools::getValue('last_name');

                if (\Tools::getValue('first_name')) {
                    $firstName = \Tools::getValue('first_name');
                }
            }

            $this->transaction = $this->additionalInformationBuilder->createAdditionalInformation(
                $this->cart,
                $this->orderId,
                $this->transaction,
                $this->currency->iso_code,
                $firstName,
                $lastName
            );
        }
    }

    /**
     * Create order and set internal order ID.
     *
     * @return int
     * @since 1.4.0
     */
    public function createOrder()
    {
        $orderManager = new OrderManager($this->module);

        $order = new \Order($orderManager->createOrder(
            $this->cart,
            OrderManager::WIRECARD_OS_STARTING,
            $this->paymentType
        ));

        $this->orderId = $order->id;

        return $order->id;
    }
}
