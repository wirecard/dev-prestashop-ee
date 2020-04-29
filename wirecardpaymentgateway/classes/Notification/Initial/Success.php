<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification\Initial;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;
use WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class Success
 * @package WirecardEE\Prestashop\Classes\Notification\Initial
 */
class Success extends AbstractSuccess implements ProcessablePaymentNotification
{
    /**
     * @var \WirecardEE\Prestashop\Classes\Service\OrderStateManagerService
     */
    private $orderStateManager;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * Success constructor.
     * @param $order
     * @param $notification
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    public function __construct($order, $notification)
    {
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
        $this->orderService = new OrderService($order);
        parent::__construct($order, $notification);
    }

    /**
     * @throws \Exception
     */
    public function beforeProcess()
    {

        $order_status = $this->orderService->getLatestOrderStatusFromHistory();
        // #TEST_STATE_LIBRARY
        $logger = new Logger();
        try {
            $nextState = $this->orderStateManager->calculateNextOrderState(
                $order_status,
                Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
                $this->notification->getData()
            );
            // #TEST_STATE_LIBRARY
            $logger->debug("Current State : {$order_status} / {$this->order->current_state}. 
            Next calculated state is {$nextState}");
            $this->order->setCurrentState($nextState);
            $this->order->save();

            $this->order_service->updateOrderPayment(
                $this->notification->getTransactionId(),
                $this->getRestAmount($nextState)
            );
        } catch (IgnorableStateException $e) {
            // #TEST_STATE_LIBRARY
            $logger->debug($e->getMessage());
        } catch (OrderStateInvalidArgumentException $e) {
            // #TEST_STATE_LIBRARY
            $logger->debug($e->getMessage());
            throw $e;
        } catch (IgnorablePostProcessingFailureException $e) {
            $logger->emergency($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        }
    }

    /**
     * @param string $order_state
     *
     * @return float|int
     * @since 2.7.0
     */
    private function getRestAmount($order_state)
    {
        $rest_amount = 0;
        $payment_processing_state = \Configuration::get('PS_OS_PAYMENT');
        $requested_amount = $this->notification->getRequestedAmount();

        if ($payment_processing_state === $order_state) {
            $rest_amount = $requested_amount->getValue();
        }

        return $rest_amount;
    }
}
