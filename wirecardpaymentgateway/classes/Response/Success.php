<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Models\Transaction;

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
    protected $order_service;
  
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
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        $dbManager = new DBTransactionManager();
        //outside of the try block. If locking fails, we don't want to attempt to release it
        $dbManager->acquireLock($this->response->getTransactionId(), 30);
        try {
            if ($this->order->getCurrentState() === \Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
                $this->order->setCurrentState(\Configuration::get(OrderManager::WIRECARD_OS_AWAITING));
                $this->order->save();

                $currency = 'EUR';
                if (key_exists('currency', $this->response->getData())) {
                    $currency = $this->response->getData()['currency'];
                }
                $amount = new Amount(0, $currency);
                if ($this->response->getTransactionType() !== \Wirecard\PaymentSdk\Transaction\Transaction::TYPE_AUTHORIZATION) {
                    $amount = $this->response->getRequestedAmount();
                }
                $this->order_service->updateOrderPayment($this->response->getTransactionId(), $amount->getValue());
            }

            $amount = $this->response->getRequestedAmount();

            $orderManager = new OrderManager();
            $transactionState = $orderManager->getTransactionState($this->response);

            Transaction::create(
                $this->order->id,
                $this->order->id_cart,
                $amount,
                $this->response,
                $transactionState,
                $this->order->reference
            );
        } finally {
            $dbManager->releaseLock($this->response->getTransactionId());
        }
    }
}
