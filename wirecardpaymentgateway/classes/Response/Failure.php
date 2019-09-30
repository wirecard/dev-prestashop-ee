<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response;

use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
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
    /** @var \Order  */
    private $order;

    /** @var FailureResponse  */
    private $response;

    /** @var ContextService  */
    private $context_service;

    /** @var OrderService  */
    private $order_service;

    /**
     * FailureResponseProcessing constructor.
     *
     * @param \Order $order
     * @param FailureResponse $response
     * @since 2.1.0
     */
    public function __construct($order, $response)
    {
        $this->order = $order;
        $this->response = $response;
        $this->context_service = new ContextService(\Context::getContext());
        $this->order_service = new OrderService($order);
    }

    /**
     * @since 2.1.0
     */
    public function process()
    {
        if ($this->order_service->isOrderState(OrderManager::WIRECARD_OS_STARTING)) {
            $this->order->setCurrentState(_PS_OS_ERROR_);
            $this->order->save();
        }

        $cart_clone = $this->order_service->getNewCartDuplicate();
        $this->context_service->setCart($cart_clone);

        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->redirectWithError($errors, 'order');
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
