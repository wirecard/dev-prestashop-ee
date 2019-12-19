<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\PostProcessing;

use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;
use WirecardEE\Prestashop\Helper\DBTransactionManager;
use WirecardEE\Prestashop\Helper\NumericHelper;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\Service\OrderService;
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
    }

    /**
     * @since 2.5.0
     */
    public function process()
    {
        parent::process();
        $tx_id = \Tools::getValue('tx_id');
        $transaction = new Transaction($tx_id);
        $transaction->markSettledAsClosed();
        $service = new OrderService($this->order);
        $service->createOrderPayment($transaction->getAmount(), $transaction->getPaymentMethod(), $transaction->getTransactionId());

        $this->context_service->setConfirmations(
            $this->getTranslatedString('success_new_transaction')
        );
    }
}
