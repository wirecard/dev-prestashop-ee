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
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;
use WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;
use WirecardEE\Prestashop\Helper\OrderManager;

class Success extends AbstractSuccess implements ProcessablePaymentNotification
{

    public function process()
    {
        if (OrderManager::isIgnorable($this->notification)) {
            return;
        }
        try {
            $parentTransaction = $this->getParentTransaction();
            $parentTransaction->markSettledAsClosed();
            //
            $order_status = (int)$this->order_service->getLatestOrderStatusFromHistory();
            $numericalValues = new OrderStateNumericalValues($this->order_service->getOrderCart()->getOrderTotal());
            try {
                $orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
                $nextState = $orderStateManager->calculateNextOrderState(
                    $order_status,
                    Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION,
                    $this->notification->getData(),
                    $numericalValues
                );
                // #TEST_STATE_LIBRARY
                $this->order->setCurrentState($nextState);
                $this->order->save();
                $this->order_service->createOrderPayment($this->notification);
            } catch (IgnorableStateException $e) {
                // #TEST_STATE_LIBRARY
                $this->logger->debug($e->getMessage(), ['ex' => get_class($e), 'method' => __METHOD__]);
            } catch (OrderStateInvalidArgumentException $e) {
                $this->logger->emergency($e->getMessage(), ['ex' => get_class($e), 'method' => __METHOD__]);
            } catch (IgnorablePostProcessingFailureException $e) {
                $this->logger->debug($e->getMessage(), ['ex' => get_class($e), 'method' => __METHOD__]);
            }

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
