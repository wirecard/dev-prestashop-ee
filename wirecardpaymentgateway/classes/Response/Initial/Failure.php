<?php


namespace WirecardEE\Prestashop\Classes\Response\Initial;


use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Helper\OrderManager;

class Failure extends  \WirecardEE\Prestashop\Classes\Response\Failure
{

    public function process()
    {
        $currentState = $this->order_service->getLatestOrderStatusFromHistory();
        $nextState = $this->orderStateManager->calculateNextOrderState(
            $currentState,
            Constant::PROCESS_TYPE_INITIAL_RETURN,
            $this->response->getData(),
            new OrderAmountCalculatorService($this->order)
        );
        if ($currentState === \Configuration::get(OrderManager::WIRECARD_OS_STARTING) && $nextState) {
            $this->order->setCurrentState($nextState); // _PS_OS_ERROR_
            $this->order->save();
            $this->order_service->updateOrderPaymentTwo($this->response->getData()['transaction-id']);
        }


        $cart_clone = $this->order_service->getNewCartDuplicate();
        $this->context_service->setCart($cart_clone);

        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->redirectWithError($errors, 'order');
    }

}