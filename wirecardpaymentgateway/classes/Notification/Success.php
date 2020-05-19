<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification;

use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use Wirecard\PaymentSdk\Response\SuccessResponse;

use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Models\Transaction;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

/**
 * Class Success
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
abstract class Success implements ProcessablePaymentNotification
{
    use TranslationHelper;

    /** @var \Order */
    protected $order;

    /** @var SuccessResponse */
    protected $notification;

    /** @var OrderService */
    protected $order_service;

    /** @var OrderManager */
    protected $order_manager;

    /** @var WirecardLogger */
    protected $logger;
    /**
     * @var ContextService
     */
    protected $contextService;

    /**
     * @var OrderAmountCalculatorService
     */
    protected $orderAmountCalculator;

    /**
     * SuccessPaymentProcessing constructor.
     *
     * @param \Order $order
     * @param SuccessResponse $notification
     * @since 2.1.0
     */
    public function __construct($order, $notification)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->order_service = new OrderService($order);
        $this->order_manager = new OrderManager();
        $this->logger = new WirecardLogger();
        $this->contextService = new ContextService(\Context::getContext());
        $this->orderAmountCalculator = new OrderAmountCalculatorService($this->order);
    }

    /**
     * @throws \Exception
     * @since 2.1.0
     */
    public function process()
    {
        try {
            $currentOrderState = (int)$this->order_service->getLatestOrderStatusFromHistory();
            try {
                $orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
                $nextOrderState = $orderStateManager->calculateNextOrderState(
                    $currentOrderState,
                    $this->getOrderStateProcessType(),
                    $this->notification->getData(),
                    $this->orderAmountCalculator
                );
                $this->order->setCurrentState($nextOrderState);
                $this->order->save();
                $this->createTransaction();
            } catch (IgnorableStateException $e) {
                $this->logger->debug($e->getMessage(), ['ex' => get_class($e), 'method' => __METHOD__]);
            } catch (OrderStateInvalidArgumentException $e) {
                $this->logger->emergency($e->getMessage(), ['ex' => get_class($e), 'method' => __METHOD__]);
            } catch (IgnorablePostProcessingFailureException $e) {
                $this->logger->debug($e->getMessage(), ['ex' => get_class($e), 'method' => __METHOD__]);
            }
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error in class:' . __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $exception->getMessage()
            );
            throw $exception;
        }
    }

    /**
     * @throws \Exception
     */
    protected function createTransaction()
    {
        if (!OrderManager::isIgnorable($this->notification)) {
            $transactionId = $this->notification->getTransactionId();
            $dbManager = new DBTransactionManager();
            //Acquire lock out of the try-catch block to prevent release on locking fail
            $dbManager->acquireLock($transactionId, 30);

            try {
                Transaction::create(
                    $this->order->id,
                    $this->order->id_cart,
                    $this->notification->getRequestedAmount(),
                    $this->notification,
                    $this->order_manager->getTransactionState($this->notification),
                    $this->order->reference
                );

                $this->order_service->addTransactionIdToOrderPayment($transactionId, $this->notification->getRequestedAmount()->getValue());
            } catch (\Exception $exception) {
                $this->logger->error(
                    'Error in class:' . __CLASS__ .
                    ' method:' . __METHOD__ .
                    ' exception: ' . $exception->getMessage()
                );
                throw $exception;
            } finally {
                $dbManager->releaseLock($this->notification->getTransactionId());
            }
        }
    }

    /**
     * @return string
     */
    abstract public function getOrderStateProcessType();
}
