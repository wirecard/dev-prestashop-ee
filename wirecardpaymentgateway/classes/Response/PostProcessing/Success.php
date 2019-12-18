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
use WirecardEE\Prestashop\Helper\Service\ContextService;

class Success extends SuccessAbstract
{
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
        $this->transaction_manager->markTransactionClosed($this->response->getParentTransactionId());
        $this->context_service->setConfirmations(
            $this->getTranslatedString('success_new_transaction')
        );
    }
}
