<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

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

    /**
     * @var ThreeDSBuilder
     * @since 2.2.0
     */
    private $threeDsBuilder;

    /** @var string */
    private $orderId;

    /** @var Transaction */
    private $transaction;

    /** @var CurrencyHelper */
    private $currencyHelper;

    /** @var ShopConfigurationService */
    private $shopConfigService;

    /**
     * TransactionBuilder constructor.
     * @param \WirecardPaymentGateway $module
     * @param \Context $context
     * @param int $cartId
     * @param string $paymentType
     * @since 2.0.0
     */
    public function __construct($module, $context, $cartId, $paymentType)
    {
        $this->module = $module;
        $this->context = $context;
        $this->paymentType = $paymentType;
        $this->shopConfigService = new ShopConfigurationService($paymentType);
        $this->cart = new \Cart((int) $cartId);
        $this->currency = new \Currency($this->cart->id_currency);
        $this->customFields = new CustomFieldCollection();
        $this->additionalInformationBuilder = new AdditionalInformationBuilder();
        $this->threeDsBuilder = new ThreeDSBuilder();
        $this->currencyHelper = new CurrencyHelper();
    }

    /**
     * Allows setting the order ID in case a pre-existing order is available.
     *
     * @param $orderId
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function buildTransaction()
    {
        /** @var Payment $payment */
        $payment = $this->module->getPaymentFromType($this->paymentType);

        /** @var Transaction $transaction */
        $this->transaction = $payment->createTransaction(
            $this->module,
            $this->cart,
            \Tools::getAllValues(),
            $this->orderId
        );

        $this->addLocale();
        $this->addAmount();
        $this->addRedirects();
        $this->addCustomField('cartId', $this->cart->id);
        $this->addTokenId();
        $this->addBasket();
        $this->addDescriptor();
        $this->addAdditionalInformation();

        if ($payment::TYPE == PaymentCreditCard::TYPE) {
            $this->addThreeDsFields();
        }

        return $this->transaction;
    }

    /**
     * Add the payment amount to the transaction
     *
     * @since 2.0.0
     */
    private function addAmount()
    {
        $this->transaction->setAmount(
            $this->currencyHelper->getAmount(
                $this->cart->getOrderTotal(),
                $this->currency->iso_code
            )
        );
    }

    /**
     * Add the necessary redirects to the transaction
     *
     * @since 2.0.0
     */
    private function addRedirects()
    {
        $redirectUrls = new Redirect(
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'success', $this->cart->id),
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'cancel', $this->cart->id),
            $this->module->createRedirectUrl($this->orderId, $this->paymentType, 'failure', $this->cart->id)
        );

        $this->transaction->setNotificationUrl(
            $this->module->createNotificationUrl(
                $this->orderId,
                $this->paymentType,
                $this->cart->id
            )
        );
        $this->transaction->setRedirect($redirectUrls);
    }

    /**
     * Add custom field to transaction
     *
     * @param $key
     * @param $value
     * @since 2.0.0
     */
    private function addCustomField($key, $value)
    {
        $this->customFields->add(new CustomField($key, $value));
        $this->transaction->setCustomFields($this->customFields);
    }

    /**
     * Set the token ID if required
     *
     * @since 2.0.0
     */
    private function addTokenId()
    {
        if ($this->transaction instanceof CreditCardTransaction && \Tools::getValue('token_id')) {
            $this->transaction->setTokenId(\Tools::getValue('token_id'));
        }
    }

    /**
     * Add the basket if required
     *
     * @since 2.0.0
     */
    private function addBasket()
    {
        if ($this->shopConfigService->getField('shopping_basket')) {
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
     * @since 2.0.0
     */
    private function addDescriptor()
    {
        if ($this->shopConfigService->getField('descriptor')) {
            $this->transaction->setDescriptor($this->additionalInformationBuilder->createDescriptor($this->orderId));
        }
    }

    /**
     * @var Transaction $transaction
     * @since 2.2.0
     */
    private function addThreeDsFields()
    {
        $configService = new ShopConfigurationService($this->paymentType);
        $challengeInd = $configService->getField('requestor_challenge');
        $this->transaction = $this->threeDsBuilder->getThreeDsTransaction(
            $this->cart,
            $this->orderId,
            $this->transaction,
            $challengeInd
        );
    }


    /**
     * Add additional information if required
     *
     * @since 2.0.0
     */
    private function addAdditionalInformation()
    {
        if ($this->shopConfigService->getField('send_additional')) {
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
     * Add locale to the transaction
     *
     * @since 2.4.0
     */
    private function addLocale()
    {
        $this->transaction->setLocale(
            $this->context->language->iso_code
        );
    }

    /**
     * Create order and set internal order ID.
     *
     * @return int
     * @throws \Exception
     * @since 2.0.0
     */
    public function createOrder()
    {
        $orderManager = new OrderManager();

        $order = new \Order($orderManager->createOrder(
            $this->cart,
            OrderManager::WIRECARD_OS_STARTING,
            $this->paymentType
        ));

        $this->orderId = $order->id;

        return $order->id;
    }
}
