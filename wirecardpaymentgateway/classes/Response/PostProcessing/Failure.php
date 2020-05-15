<?php


namespace WirecardEE\Prestashop\Classes\Response\PostProcessing;


use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Helper\OrderManager;

class Failure extends \WirecardEE\Prestashop\Classes\Response\Failure
{
    public function process()
    {
        $currentState = $this->order_service->getLatestOrderStatusFromHistory();
        try {
            $nextState = $this->orderStateManager->calculateNextOrderState(
                $currentState,
                    Constant::PROCESS_TYPE_POST_PROCESSING_RETURN,
                $this->response->getData(),
                new OrderAmountCalculatorService($this->order)
            );
            if ($currentState === \Configuration::get(OrderManager::WIRECARD_OS_STARTING)) {
                $this->order->setCurrentState($nextState); // _PS_OS_ERROR_
                $this->order->save();
                $this->order_service->updateOrderPaymentTwo($this->response->getData()['transaction-id']);
            }
        } catch (IgnorableStateException $e) {
            $this->logger->debug($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (OrderStateInvalidArgumentException $e) {
            $this->logger->debug('$e->getMessage()', ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (IgnorablePostProcessingFailureException $e) {
            $this->logger->debug('$e->getMessage()', ['exception_class' => get_class($e), 'method' => __METHOD__]);
                $this->processBackend();
                return;
        }

        $cart_clone = $this->order_service->getNewCartDuplicate();
        $this->context_service->setCart($cart_clone);

        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->redirectWithError($errors, 'order');
    }

    private function processBackend()
    {
        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->setErrors(\Tools::displayError(implode('<br>', $errors)));
    }

}