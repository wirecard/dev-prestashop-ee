<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use Wirecard\PaymentSdk\Response\FailureResponse;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class Failure
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
final class Failure implements ProcessablePaymentNotification
{
    /** @var \Order */
    private $order;

    /** @var FailureResponse */
    private $notification;

    /** @var OrderService */
    private $orderService;

    /**
     * @var \WirecardEE\Prestashop\Classes\Service\OrderStateManagerService
     */
    private $orderStateManager;

    /**
     * FailurePaymentProcessing constructor.
     *
     * @param \Order $order
     * @param FailureResponse $notification
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     * @since 2.1.0
     */
    public function __construct($order, $notification)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->orderService = new OrderService($order);
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        $logger = new Logger();
        // #TEST_STATE_LIBRARY
        $logger->debug("NOTIFICATION PROCESS");
        $currentState = $this->orderService->getLatestOrderStatusFromHistory();
        // #TEST_STATE_LIBRARY
        $logger->debug(print_r($this->notification->getData(), true));
        try {
            $nextState = $this->orderStateManager->calculateNextOrderState(
                $currentState,
                Constant::PROCESS_TYPE_INITIAL_NOTIFICATION,
                $this->notification->getData()
            );
            // #TEST_STATE_LIBRARY
            $logger->debug("Current State : {$currentState}. Next calculated state is {$nextState}");
            if ($currentState !== $nextState) {
                $this->order->setCurrentState($nextState);
                $this->order->save();
                $this->orderService->updateOrderPayment($this->notification->getData()['transaction-id'], 0);
            }
        } catch (IgnorableStateException $e) {
            // #TEST_STATE_LIBRARY
            $logger->debug($e->getMessage());
        } catch (OrderStateInvalidArgumentException $e) {
            // #TEST_STATE_LIBRARY
            $logger->debug($e->getMessage());
        } catch (IgnorablePostProcessingFailureException $e) {
            $logger->emergency($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        }
    }
}
