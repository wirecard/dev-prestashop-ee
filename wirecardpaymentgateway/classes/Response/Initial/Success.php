<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\Initial;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Transaction as TransactionTypes;
use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\PaymentPoiPia;

class Success extends SuccessAbstract
{
    /**
     * @var ContextService
     */
    private $context_service;

    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @var \Customer
     */
    private $customer;

    /**
     * @var \WirecardPaymentGateway
     */
    private $module;

    /**
     * Success constructor.
     * @param $order
     * @param $response
     * @since 2.5.0
     */
    public function __construct($order, $response)
    {
        parent::__construct($order, $response);

        $this->context_service = new ContextService(\Context::getContext());
        $this->cart = $this->orderService->getOrderCart();
        $this->customer = new \Customer((int)$this->cart->id_customer);
        $this->module = \Module::getInstanceByName('wirecardpaymentgateway');
    }

    /**
     * @since 2.10.0
     */
    protected function beforeProcess()
    {
        $logger = new Logger();
        $logger->debug("BEFORE PROCESS");
        $order_status = $this->orderService->getLatestOrderStatusFromHistory();
        // #TEST_STATE_LIBRARY
        $logger->debug(print_r($this->response->getData(), true));
        try {
            $nextState = $this->module->orderStateManager()->calculateNextOrderState(
                $order_status,
                Constant::PROCESS_TYPE_RETURN,
                $this->response->getData()
            );
            $logger->debug("Current State : {$order_status}. Next calculated state is {$nextState}");
            $this->order->setCurrentState($nextState);
            $this->order->save();
        } catch (IgnorableStateException $e) {
            // #TEST_STATE_LIBRARY
            $logger->debug($e->getMessage());
        } catch (OrderStateInvalidArgumentException $e) {
            // #TEST_STATE_LIBRARY
            $logger->debug($e->getMessage());
        }

        if ($order_status === \Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            $this->onOrderStateStarted();
        }
    }

    /**
     * @inheritDoc
     */
    protected function afterProcess()
    {
        (new Logger())->debug("AFTER PROCESS");
        if ($this->isPiaPayment()) {
            $this->context_service->setPiaCookie($this->response);
        }

        //redirect to success page
        \Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='
            . $this->cart->id . '&id_module='
            . $this->module->id . '&id_order='
            . $this->order->id . '&key='
            . $this->customer->secure_key
        );
    }

    private function onOrderStateStarted()
    {
        $currency = 'EUR';
        if (key_exists('currency', $this->response->getData())) {
            $currency = $this->response->getData()['currency'];
        }
        $amount = new Amount(0, $currency);
        if ($this->response->getTransactionType() !== TransactionTypes::TYPE_AUTHORIZATION) {
            $amount = $this->response->getRequestedAmount();
        }
        $this->orderService->updateOrderPayment($this->response->getTransactionId(), $amount->getValue());
    }

    /**
     * Check if the payment method is PoiPia and the payment type is Pia
     *
     * @return bool
     * @since 2.5.0
     */
    private function isPiaPayment()
    {
        $configuration_service = new ShopConfigurationService(PaymentPoiPia::TYPE);
        return $this->response->getPaymentMethod() === PaymentPoiPia::TYPE &&
            $configuration_service->getField('payment_type') === PaymentPoiPia::PIA;
    }
}
