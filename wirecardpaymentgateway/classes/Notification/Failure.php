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
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;
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
     * @var Logger
     */
    protected $logger;
    /**
     * @var bool
     */
    private $isPostProcessing = false;

    /**
     * FailurePaymentProcessing constructor.
     *
     * @param \Order $order
     * @param FailureResponse $notification
     * @param bool $isPostProcessing
     * @throws OrderStateInvalidArgumentException
     * @since 2.1.0
     */
    public function __construct($order, $notification, $isPostProcessing)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->orderService = new OrderService($order);
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
        $this->logger = new Logger();
        $this->isPostProcessing = $isPostProcessing;
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        // #TEST_STATE_LIBRARY
        $currentState = $this->orderService->getLatestOrderStatusFromHistory();
        // #TEST_STATE_LIBRARY
        try {
            $numericalValues = new OrderStateNumericalValues($this->orderService->getOrderCart()->getOrderTotal());
            $orderStateProcessType = ($this->isPostProcessing) ?
                Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION :
                Constant::PROCESS_TYPE_INITIAL_NOTIFICATION;
            $nextState = $this->orderStateManager->calculateNextOrderState(
                $currentState,
                $orderStateProcessType,
                $this->notification->getData(),
                $numericalValues
            );
            // #TEST_STATE_LIBRARY
            if ($currentState !== $nextState) {
                $this->order->setCurrentState($nextState);
                $this->order->save();
            }
        } catch (IgnorableStateException $e) {
            // #TEST_STATE_LIBRARY
            $this->logger->debug($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (OrderStateInvalidArgumentException $e) {
            $this->logger->emergency($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (IgnorablePostProcessingFailureException $e) {
            $this->logger->debug($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        }
    }
}
