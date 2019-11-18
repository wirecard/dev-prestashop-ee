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
use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Class Success
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
abstract class Success implements ProcessablePaymentResponse
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = 'success';

    /** @var \Order  */
    protected $order;

    /** @var SuccessResponse  */
    protected $response;

    /** @var OrderService */
    private $order_service;

    /** @var \Cart */
    protected $cart;

    /** @var \Customer */
    protected $customer;

    /** @var string */
    private $process_type;

    /** @var \WirecardPaymentGateway */
    protected $module;

    /** @var ContextService  */
    protected $context_service;

    /** @var ShopConfigurationService */
    protected $configuration_service;

    /** @var DBTransactionManager */
    protected $transaction_manager;
  
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
        $this->context_service = new ContextService(\Context::getContext());
        $this->transaction_manager = new DBTransactionManager();
        $this->cart = $this->order_service->getOrderCart();
        $this->customer = new \Customer((int) $this->cart->id_customer);
        $this->module = \Module::getInstanceByName('wirecardpaymentgateway');
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
    }
}
