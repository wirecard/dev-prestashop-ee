<?php

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\Amount;

class TransactionBuilder {
    /** @var \WirecardPaymentGateway */
    private $module;

    /** @var \Context */
    private $context;

    /** @var \Cart */
    private $cart;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $orderId;

    /** @var AdditionalInformation */
    private $additionalInformation;

    public function __construct($module, $context, $cart, $paymentType) {
        $this->module = $module;
        $this->context = $context;
        $this->cart = $cart;
        $this->paymentType = $paymentType;

        $this->additionalInformation = new AdditionalInformation();
    }

    /**
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     * @throws \Exception
     */
    public function buildTransaction() {
        if (!isset($this->orderId)) {
            throw new \Exception("An order needs to be created before building a transaction");
        }

        $payment = $this->module->getPaymentFromType($this->paymentType);
        $cartId = $this->cart->id;

        $amount = round($this->cart->getOrderTotal(), 2);
        $currency = new \Currency($this->cart->id_currency);
        $redirectUrls = new Redirect(
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'success'),
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'cancel'),
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'failure')
        );

        /** @var Transaction $transaction */
        $transaction = $payment->createTransaction($this->module, $this->cart, \Tools::getAllValues(), $this->orderId);
        $transaction->setNotificationUrl($this->module->createNotificationUrl($cartId, $this->paymentType));
        $transaction->setRedirect($redirectUrls);
        $transaction->setAmount(new Amount((float)$amount, $currency->iso_code));

        $customFields = new CustomFieldCollection();
        $customFields->add(new CustomField('orderId', $this->orderId));
        $transaction->setCustomFields($customFields);

        if ($this->module->getConfigValue($this->paymentType, 'shopping_basket')) {
            $transaction->setBasket($this->additionalInformation->createBasket($this->cart, $transaction, $currency->iso_code));
        }

        if ($this->module->getConfigValue($this->paymentType, 'descriptor')) {
            $transaction->setDescriptor($this->additionalInformation->createDescriptor($this->orderId));
        }

        if ($this->module->getConfigValue($this->paymentType, 'send_additional')) {
            $firstName = null;
            $lastName = null;

            if (\Tools::getValue('last_name')) {
                $lastName = \Tools::getValue('last_name');

                if (\Tools::getValue('first_name')) {
                    $firstName = \Tools::getValue('first_name');
                }
            }

            $transaction = $this->additionalInformation->createAdditionalInformation(
                $this->cart,
                $this->orderId,
                $transaction,
                $currency->iso_code,
                $firstName,
                $lastName
            );
        }

        return $transaction;
    }

    /**
     * Create order
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