<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification;

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
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
    /** @var \Order  */
    private $order;

    /** @var FailureResponse|SuccessResponse  */
    private $notification;

    /** @var string */
    private $processType;
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
    public function __construct($order, $notification, $processType = ProcessType::PROCESS_RESPONSE)
    {
        $this->order = $order;
        $this->notification = $notification;
        $this->processType = $processType;
        $this->logger = new WirecardLogger();
    }

    /**
     * @return Failure|Success
     * @since 2.1.0
     */
    public function getPaymentProcessing()
    {
        //$this->logger->debug(__METHOD__, ['line' => __LINE__]);
        if ($this->notification instanceof SuccessResponse) {
            if ($this->processType === ProcessType::PROCESS_RESPONSE) {
                return new InitialSuccess($this->order, $this->notification);
            }

            return new PostProcessingSuccess($this->order, $this->notification);
        }

        return new Failure($this->order, $this->notification);
    }
}
