<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Models\Transaction;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Classes\Service\TransactionPostProcessingService;
use WirecardEE\Prestashop\Classes\Service\TransactionPossibleOperationService;

/**
 * Class WirecardTransactions
 *
 * @property WirecardPaymentGateway module
 * @property Transaction $object
 * @since 1.0.0
 */
class WirecardTransactionsController extends ModuleAdminController
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardtransactions";

    /** @var string */
    const BUTTON_RESET = "submitResetwirecard_payment_gateway_tx";

    /** @var ContextService */
    protected $context_service;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wirecard_payment_gateway_tx';
        $this->className = Transaction::class;
        $this->lang = false;
        $this->addRowAction('view');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->context_service = new ContextService($this->context);
        $this->identifier = 'tx_id';

        $this->module = Module::getInstanceByName('wirecardpaymentgateway');

        $this->_defaultOrderBy = 'tx_id';
        $this->_defaultOrderWay = 'DESC';
        $this->_use_found_rows = true;

        $this->fields_list = (new Transaction())->getFieldList();

        if (Tools::isSubmit(self::BUTTON_RESET)) {
            $this->processResetFilters();
        }
        $this->processFilter();

        parent::__construct();
        $this->tpl_folder = 'backend/';
    }

    /**
     * Render detail transaction view
     *
     * @return mixed
     * @throws Exception
     * @since 1.0.0
     * @since 2.4.0 Major refactoring and simplification
     */
    public function renderView()
    {
        $this->validateTransaction($this->object);
        $possibleOperationService = new TransactionPossibleOperationService($this->object);
        $paymentModel = PaymentProvider::getPayment($this->object->getPaymentMethod());

        $transactionModel = new Transaction($this->object->tx_id);

        // These variables are available in the Smarty context
        $this->tpl_view_vars = [
            'current_index'       => self::$currentIndex,
            'back_link'           => (new Link())->getAdminLink('WirecardTransactions', true),
            'payment_method'      => $paymentModel->getName(),
            'possible_operations' => $possibleOperationService->getPossibleOperationList(),
            'transaction'         => $this->object->toViewArray(),
            'remaining_delta_amount' => $transactionModel->getRemainingAmount(),
        ];

        return parent::renderView();
    }

    /**
     * Process follow-up actions such as refund/cancel/etc
     *
     * @throws Exception
     * @since 1.0.0
     * @since 2.4.0 Major refactoring
     * @return bool|ObjectModel
     */
    public function postProcess()
    {
        $operation = \Tools::getValue('operation');
        $transactionId = \Tools::getValue('transaction');

        // This prevents the function from running on the list page
        if (!$operation || !$transactionId) {
            return;
        }

        $parentTransaction = new Transaction($transactionId);
        $delta_amount = Tools::getValue('partial-delta-amount', $parentTransaction->getAmount());

        $transactionPostProcessingService = new TransactionPostProcessingService($operation, $transactionId);
        $transactionPostProcessingService->process($delta_amount);
        if (!empty($transactionPostProcessingService->getErrors())) {
            $this->errors[] = implode("<br />", $transactionPostProcessingService->getErrors());
        }

        return parent::postProcess();
    }

    /**
     * Checks that the transaction data is a valid object from the PrestaShop
     * database and adds an error if this is not the case.
     *
     * @param object $data
     * @throws PrestaShopException
     * @since 2.4.0
     */
    private function validateTransaction($data)
    {
        if (!Validate::isLoadedObject($data)) {
            $this->errors[] = \Tools::displayError(
                $this->getTranslatedString('error_no_transaction')
            );
        }
    }
}
