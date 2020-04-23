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
     * @var \WirecardPaymentGateway
     */
    private $module;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * Success constructor.
     * @param $order
     * @param $notification
     */
    public function __construct($order, $notification)
    {
        $this->module = \Module::getInstanceByName('wirecardpaymentgateway');
        $this->orderService = new OrderService($order);
        parent::__construct($order, $notification);
    }

    /**
     * @throws \Exception
     */
    public function beforeProcess()
    {

        $order_status = $this->orderService->getLatestOrderStatusFromHistory();
        $logger = new Logger();
        try {
            $nextState = $this->module->orderStateManager()->calculateNextOrderState(
                $order_status,
                Constant::PROCESS_TYPE_NOTIFICATION,
                $this->notification->getData()
            );
            $logger->debug("Current State : {$order_status}. Next calculated state is {$nextState}");
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
