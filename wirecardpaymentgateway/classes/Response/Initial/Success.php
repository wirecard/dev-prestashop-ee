<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\Initial;

use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;
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
        $this->customer = new \Customer((int) $this->cart->id_customer);
        $this->module = \Module::getInstanceByName('wirecardpaymentgateway');
    }

    /**
     * @since 2.5.0
     */
    public function process()
    {
        parent::process();

        if ($this->isPiaPayment()) {
            $this->context_service->setPiaCookie($this->response);
        }

        //redirect to success page
        \Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='
            .$this->cart->id.'&id_module='
            .$this->module->id.'&id_order='
            .$this->order->id.'&key='
            .$this->customer->secure_key
        );
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
