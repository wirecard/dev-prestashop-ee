<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * Class Success
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
final class Success implements ProcessablePaymentResponse
{
    /** @var \Order  */
    private $order;

    /** @var SuccessResponse  */
    private $response;

    /** @var OrderService */
    private $order_service;

    /** @var \Cart */
    private $cart;

    /** @var \Customer */
    private $customer;

    /** @var \WirecardPaymentGateway */
    private $module;

    /** @var ContextService  */
    private $context_service;

    /** @var ShopConfigurationService */
    private $configuration_service;
  
    /**
     * SuccessResponseProcessing constructor.
     *
     * @param \Order $order
     * @param SuccessResponse $response
     * @since 2.1.0
     */
    public function __construct($order, $response)
    {
        $this->order = $order;
        $this->response = $response;
        $this->order_service = new OrderService($order);
        $this->cart = $this->order_service->getOrderCart();
        $this->customer = new \Customer((int) $this->cart->id_customer);
        $this->module = \Module::getInstanceByName('wirecardpaymentgateway');
        $this->context_service = new ContextService(\Context::getContext());
        $this->configuration_service = new ShopConfigurationService('wiretransfer');
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        if ($this->order->getCurrentState() === \Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
            $this->order->setCurrentState(\Configuration::get(OrderManager::WIRECARD_OS_AWAITING));
            $this->order->save();

            $this->order_service->updateOrderPayment($this->response->getTransactionId(), 0);
        }

        if ($this->response->getPaymentMethod() === 'wiretransfer' &&
            $this->configuration_service->getField('payment_type') === 'pia') {
            $this->context_service->setPiaCookie($this->response);
        }

        \Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='
            .$this->cart->id.'&id_module='
            .$this->module->id.'&id_order='
            .$this->order->id.'&key='
            .$this->customer->secure_key
        );
    }
}
