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

use WirecardEE\Prestashop\Helper\OrderManager;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;

/**
 * Class Cancel
 * @since 2.1.0
 *@package WirecardEE\Prestashop\Classes\Response
 */
final class Cancel implements ProcessablePaymentResponse
{
    const CANCEL_PAYMENT_STATE = 'cancel';

    /** @var \Order */
    private $order;

    /** @var ContextService */
    private $context_service;

    /** @var OrderService */
    private $order_service;

    /**
     * CancelResponseProcessing constructor.
     *
     * @param \Order $order
     */
    public function __construct($order)
    {
        $this->order = $order;
        $this->context_service = new ContextService(\Context::getContext());
        $this->order_service = new OrderService($order);
    }

    /**
     * @throws \Exception
     * @since 2.1.0
     */
    public function process()
    {
        if ($this->order_service->isOrderState(OrderManager::WIRECARD_OS_STARTING)) {
            $this->order->setCurrentState(\Configuration::get('PS_OS_CANCELED'));
            $cart_clone = $this->order_service->getNewCartDuplicate();
            $this->context_service->setCart($cart_clone);

            \Tools::redirect('index.php?controller=order');
        }

        throw new \Exception('The order is not cancelable');
    }
}
