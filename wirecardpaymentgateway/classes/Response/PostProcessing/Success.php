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
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;
use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\NumericHelper;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Models\Transaction;

class Success extends SuccessAbstract
{
    use NumericHelper;
    /**
     * @var DBTransactionManager
     */
    private $transaction_manager;

    /**
     * @var ContextService
     */
    private $context_service;

    /**
     * @var \WirecardEE\Prestashop\Classes\Service\OrderStateManagerService
     */
    private $orderStateManager;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Success constructor.
     * @param $order
     * @param $response
     * @since 2.5.0
     */
    public function __construct($order, $response)
    {
        parent::__construct($order, $response);

        $this->transaction_manager = new DBTransactionManager();
        $this->context_service = new ContextService(\Context::getContext());
        $this->orderStateManager = \Module::getInstanceByName('wirecardpaymentgateway')->orderStateManager();
        $this->logger = new Logger();
    }

    /**
     * @since 2.5.0
     */
    public function process()
    {
        $this->logger->debug(__METHOD__, ['line' => __LINE__]);
        parent::process();
        $transaction = new Transaction(\Tools::getValue('tx_id'));
        $transaction->markSettledAsClosed();

        $this->context_service->setConfirmations(
            $this->getTranslatedString('success_new_transaction')
        );
        $order_status = (int)$this->orderService->getLatestOrderStatusFromHistory();
        $numericalValues = new OrderStateNumericalValues($this->orderService->getOrderCart()->getOrderTotal());
        try {
            $this->orderStateManager->calculateNextOrderState(
                $order_status,
                Constant::PROCESS_TYPE_POST_PROCESSING_RETURN,
                $this->response->getData(),
                $numericalValues
            );
        } catch (IgnorableStateException $exception) {
            //do nothing, as expected
        }
    }
}
