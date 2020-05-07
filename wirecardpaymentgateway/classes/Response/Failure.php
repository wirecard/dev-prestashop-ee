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
use WirecardEE\Prestashop\Classes\ProcessType;
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * Class Failure
 * @package WirecardEE\Prestashop\Classes\Response
 * @since 2.1.0
 */
final class Failure implements ProcessablePaymentResponse
{
    /** @var \Order */
    private $order;

    /** @var FailureResponse */
    private $response;

    /** @var ContextService */
    private $context_service;

    /** @var OrderService */
    private $order_service;

    /** @var string */
    private $processType;

    /**
     * @var \WirecardEE\Prestashop\Classes\Service\OrderStateManagerService
     */
    private $orderStateManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * FailureResponseProcessing constructor.
     *
     * @param \Order $order
     * @param FailureResponse $response
     * @param string $processType
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     * @since 2.1.0
     */
    public function __construct($order, $response, $processType)
    {
        $this->order = $order;
        $this->response = $response;
        $this->processType = $processType;
        $this->context_service = new ContextService(\Context::getContext());
        $this->order_service = new OrderService($order);
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
        $this->logger = new Logger();
    }


    /**
     * @since 2.10.0
     */
    public function process()
    {
        $currentState = $this->order_service->getLatestOrderStatusFromHistory();
        try {
            $orderTotal = $this->order_service->getOrderCart()->getOrderTotal();
            $numericalValues = new OrderStateNumericalValues($orderTotal);
            $nextState = $this->orderStateManager->calculateNextOrderState(
                $currentState,
                Constant::PROCESS_TYPE_INITIAL_RETURN,
                $this->response->getData(),
                $numericalValues
            );
            // #TEST_STATE_LIBRARY
            if ($currentState === \Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
                $this->order->setCurrentState($nextState); // _PS_OS_ERROR_
                $this->order->save();
                $this->order_service->updateOrderPaymentTwo($this->response->getData()['transaction-id']);
            }

            if ($this->processType === ProcessType::PROCESS_BACKEND) {
                $this->processBackend();
                return;
            }
        } catch (IgnorableStateException $e) {
            // #TEST_STATE_LIBRARY
            $this->logger->debug($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (OrderStateInvalidArgumentException $e) {
            $this->logger->emergency($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (IgnorablePostProcessingFailureException $e) {
            $this->logger->debug($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        }


        $cart_clone = $this->order_service->getNewCartDuplicate();
        $this->context_service->setCart($cart_clone);

        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->redirectWithError($errors, 'order');
    }

    private function processBackend()
    {
        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->setErrors(
            \Tools::displayError(
                join('<br>', $errors)
            )
        );
    }

    /**
     * @param StatusCollection $statuses
     *
     * @return array
     * @since 2.1.0
     */
    private function getErrorsFromStatusCollection($statuses)
    {
        $error = array();

        foreach ($statuses->getIterator() as $status) {
            array_push($error, $status->getDescription());
        }

        return $error;
    }
}
