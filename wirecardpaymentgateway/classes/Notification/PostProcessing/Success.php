<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification\PostProcessing;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;
use WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\Service\OrderService;

class Success extends AbstractSuccess implements ProcessablePaymentNotification
{
    /** @var WirecardLogger  */
    private $logger;

//    /**
//     * Success constructor.
//     *
//     * @since 2.7.0
//     */
//    public function __construct()
//    {
//        $this->orderService = new OrderService($order);
//        $this->logger = new WirecardLogger();
//    }

    public function process()
    {
        $this->logger->debug(__METHOD__, ['line' => __LINE__]);
        if (OrderManager::isIgnorable($this->notification)) {
            return;
        }
        try {
            $parentTransaction = $this->getParentTransaction();
            $parentTransaction->markSettledAsClosed();
            //
            $order_status = (int)$this->order_service->getLatestOrderStatusFromHistory();
            $this->logger->debug('postprocessing order state', compact('order_status'));
            $numericalValues = new OrderStateNumericalValues($this->order_service->getOrderCart()->getOrderTotal());
            try {
                $orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
                $nextState = $orderStateManager->calculateNextOrderState(
                    $order_status,
                    Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
                    $this->notification->getData(),
                    $numericalValues
                );
                $this->logger->debug('XXX calculated next state', compact('nextState'));
                // #TEST_STATE_LIBRARY
                $this->logger->debug("XXX Current State : {$order_status}. Next calculated state is {$nextState}");
                $this->order->setCurrentState($nextState);
                $this->order->save();
            } catch (IgnorableStateException $exception) {
                //do nothing, as expected
            } catch(\Exception $exception) {
                $this->logger->debug('exception in post-processing notification', ['ex' => get_class($exception)]);
            }
            //
            $parentTransaction->updateOrder(
                $this->order,
                $this->notification,
                $this->order_manager,
                $this->order_service
            );
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error in class:'. __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $exception->getMessage()
            );
            throw $exception;
        }
    }
}
