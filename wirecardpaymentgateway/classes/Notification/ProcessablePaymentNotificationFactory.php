<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification;

use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\Transaction;
use WirecardEE\Prestashop\Classes\Notification\Initial\Success as InitialSuccess;
use WirecardEE\Prestashop\Classes\Notification\PostProcessing\Success as PostProcessingSuccess;
use WirecardEE\Prestashop\Classes\ProcessType;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

/**
 * Class ProcessablePaymentNotificationFactory
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
class ProcessablePaymentNotificationFactory
{
    /** @var \Order */
    private $order;

    /** @var FailureResponse|SuccessResponse */
    private $notification;

    /**
     * @var WirecardLogger
     */
    private $logger;

    /**
     * PaymentProcessingFactory constructor.
     *
     * @param \Order $order
     * @param SuccessResponse|FailureResponse $notification
     * @since 2.1.0
     */
    public function __construct($order, $notification)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->logger = new WirecardLogger();
    }

    private function isPostProcessing()
    {
        $types = [
            Transaction::TYPE_CAPTURE_AUTHORIZATION,
            Transaction::TYPE_VOID_AUTHORIZATION,
            Transaction::TYPE_CREDIT,
            Transaction::TYPE_REFUND_CAPTURE,
            Transaction::TYPE_REFUND_DEBIT,
            Transaction::TYPE_REFUND_REQUEST,
            Transaction::TYPE_VOID_CAPTURE,
            Transaction::TYPE_REFUND_PURCHASE,
            Transaction::TYPE_REFERENCED_PURCHASE,
            Transaction::TYPE_VOID_PURCHASE,
            Transaction::TYPE_VOID_DEBIT,
            Transaction::TYPE_VOID_REFUND_CAPTURE,
            Transaction::TYPE_VOID_REFUND_PURCHASE,
            Transaction::TYPE_VOID_CREDIT,
        ];
        return in_array($this->notification->getTransactionType(), $types);
    }

    /**
     * @return Failure|Success
     * @since 2.1.0
     */
    public function getPaymentProcessing()
    {
        if ($this->notification instanceof SuccessResponse) {
            if ($this->isPostProcessing()) {
                return new PostProcessingSuccess($this->order, $this->notification);
            }
            try {
                return new InitialSuccess($this->order, $this->notification);
            } catch (OrderStateInvalidArgumentException $e) {
                return new Failure($this->order, $this->notification);//TODO: review ok?
            }
        }

        return new Failure($this->order, $this->notification);
    }
}
