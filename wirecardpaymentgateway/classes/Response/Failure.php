<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * Class Failure
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
abstract class Failure implements ProcessablePaymentResponse
{
    /** @var \Order */
    protected $order;

    /** @var FailureResponse */
    protected $response;

    /** @var ContextService */
    protected $context_service;

    /** @var OrderService */
    protected $order_service;

    /**
     * @var \WirecardEE\Prestashop\Classes\Service\OrderStateManagerService
     */
    protected $orderStateManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * FailureResponseProcessing constructor.
     *
     * @param \Order $order
     * @param FailureResponse $response
     * @throws OrderStateInvalidArgumentException
     * @since 2.1.0
     */
    public function __construct($order, $response)
    {
        $this->order = $order;
        $this->response = $response;
        $this->context_service = new ContextService(\Context::getContext());
        $this->order_service = new OrderService($order);
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
        $this->logger = new Logger();
    }

    /**
     * @since 2.10.0
     */
    abstract public function process();

    /**
     * @param StatusCollection $statuses
     *
     * @return array
     * @since 2.1.0
     */
    protected function getErrorsFromStatusCollection($statuses)
    {
        $error = array();

        foreach ($statuses->getIterator() as $status) {
            array_push($error, $status->getDescription());
        }

        return $error;
    }
}
