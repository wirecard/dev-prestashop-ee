<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\PostProcessing;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;
use WirecardEE\Prestashop\Classes\Service\OrderAmountCalculatorService;
use WirecardEE\Prestashop\Classes\Service\OrderStateManagerService;
use WirecardEE\Prestashop\Helper\Service\ContextService;

class Success extends SuccessAbstract
{
    /**
     * @var OrderStateManagerService
     */
    private $orderStateManager;

    /** @var ContextService */
    private $contextService;

    /**
     * Success constructor.
     * @param $order
     * @param $response
     * @throws OrderStateInvalidArgumentException
     * @since 2.5.0
     */
    public function __construct($order, $response)
    {
        parent::__construct($order, $response);
        $this->contextService = new ContextService(\Context::getContext());
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
    }

    /**
     * @since 2.5.0
     *
     * Do not update the order state, as this is done in the notification handling later on.
     *
     * We do this to as a sanity check.
     */
    public function process()
    {
        $this->contextService->setConfirmations(
            $this->getTranslatedString('success_new_transaction')
        );
        $order_status = (int)$this->orderService->getLatestOrderStatusFromHistory();
        $this->orderStateManager->calculateNextOrderState(
            $order_status,
            Constant::PROCESS_TYPE_POST_PROCESSING_RETURN,
            $this->response->getData(),
            new OrderAmountCalculatorService($this->order)
        );
    }
}
