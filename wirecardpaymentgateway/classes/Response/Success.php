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
use WirecardEE\Prestashop\Helper\TransactionManager;
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Class Success
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
final class Success implements ProcessablePaymentResponse
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = 'success';

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

    /** @var string */
    private $process_type;

    /** @var \WirecardPaymentGateway */
    private $module;

    /** @var ContextService  */
    private $context_service;

    /** @var ShopConfigurationService */
    private $configuration_service;

    /** @var TransactionManager */
    private $transaction_manager;
  
    /**
     * SuccessResponseProcessing constructor.
     *
     * @param \Order $order
     * @param SuccessResponse $response
     * @param $process_type
     * @since 2.1.0
     */
    public function __construct($order, $response, $process_type)
    {
        $this->order = $order;
        $this->response = $response;
        $this->process_type = $process_type;

        $this->order_service = new OrderService($order);
        $this->context_service = new ContextService(\Context::getContext());
        $this->transaction_manager = new TransactionManager();
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

        if ($this->process_type === ProcessablePaymentResponseFactory::PROCESS_BACKEND) {
            $this->processBackend();
            return;
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

    protected function processBackend() {
        $transaction_id = \Tools::getValue('tx_id');

        $this->transaction_manager->markTransactionClosed($transaction_id);
        $this->context_service->setConfirmations(
            $this->getTranslatedString('success_new_transaction')
        );
    }
}
