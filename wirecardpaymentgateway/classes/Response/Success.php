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

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\ModuleHelper;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\PaymentPoiPia;

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
        $this->configuration_service = new ShopConfigurationService(PaymentPoiPia::TYPE);
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
