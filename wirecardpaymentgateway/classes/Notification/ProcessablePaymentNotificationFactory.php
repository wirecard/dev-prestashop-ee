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
use WirecardEE\Prestashop\Classes\Notification\Initial\Failure as InitialFailure;
use WirecardEE\Prestashop\Classes\Notification\Initial\Success as InitialSuccess;
use WirecardEE\Prestashop\Classes\Notification\PostProcessing\Failure as PostProcessingFailure;
use WirecardEE\Prestashop\Classes\Notification\PostProcessing\Success as PostProcessingSuccess;
use WirecardEE\Prestashop\Classes\ProcessablePaymentFactory;

/**
 * Class ProcessablePaymentNotificationFactory
 * @since 2.1.0
 * @package WirecardEE\Prestashop\Classes\Notification
 */
class ProcessablePaymentNotificationFactory extends ProcessablePaymentFactory
{
    /** @var \Order */
    private $order;

    /** @var FailureResponse|SuccessResponse */
    private $notification;

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
    }

    /**
     * @return Failure|Success
     * @throws OrderStateInvalidArgumentException
     * @since 2.1.0
     */
    public function getPaymentProcessing()
    {
        if ($this->notification instanceof SuccessResponse) {
            if ($this->isPostProcessing($this->notification)) {
                return new PostProcessingSuccess($this->order, $this->notification);
            }
            return new InitialSuccess($this->order, $this->notification);
        }
        if ($this->isPostProcessing($this->notification)) {
            return new PostProcessingFailure($this->order, $this->notification);
        }
        return new InitialFailure($this->order, $this->notification);
    }
}
