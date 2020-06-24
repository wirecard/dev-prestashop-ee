<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class Failure
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
abstract class Failure implements ProcessablePaymentResponse
{

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
     * @param OrderService $order_service
     * @param FailureResponse $response
     * @since 2.1.0
     */
    public function __construct(OrderService $order_service, $response)
    {
        $this->response = $response;
        $this->context_service = new ContextService(\Context::getContext());
        $this->order_service = $order_service;
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

        /** @var $status Status */
        foreach ($statuses->getIterator() as $status) {
            array_push($error, $status->getDescription());
        }

        return $error;
    }

    /**
     * @param $processType
     * @throws \PrestaShopException
     * @since 2.10.0
     */
    protected function processForType($processType)
    {
        $currentState = $this->order_service->getLatestOrderStatusFromHistory();
        $nextState = $this->orderStateManager->calculateNextOrderState(
            $currentState,
            $processType,
            $this->response->getData(),
            new OrderAmountCalculatorService($this->order_service->getOrder())
        );
        if ($currentState === \Configuration::get(OrderManager::WIRECARD_OS_STARTING) && $nextState) {
            $order = $this->order_service->getOrder();
            $order->setCurrentState($nextState);
            $order->save();
            $this->order_service->updateOrderPayment(
                $this->response->getData()['transaction-id'],
                $this->response->getRequestedAmount()->getValue()
            );
        }
        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $cart_clone = $this->order_service->getNewCartDuplicate();
        $this->context_service->setCart($cart_clone);
        $this->context_service->redirectWithError($errors, 'order');
    }
}
