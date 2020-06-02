<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Classes\Service\TransactionPossibleOperationService;
use WirecardEE\Prestashop\Classes\Service\TransactionPostProcessingService;
use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Models\Transaction;

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
	 * Render transaction table view
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 2.10.0
	 */
    public function renderList() {

	    if (!($this->fields_list && is_array($this->fields_list))) {
		    return false;
	    }
	    $this->getList($this->context->language->id);

	    // If list has 'active' field, we automatically create bulk action
	    if (isset($this->fields_list) && is_array($this->fields_list) && array_key_exists('active', $this->fields_list)
	        && !empty($this->fields_list['active'])) {
		    if (!is_array($this->bulk_actions)) {
			    $this->bulk_actions = array();
		    }

		    $this->bulk_actions = array_merge(array(
			    'enableSelection' => array(
				    'text' => $this->l('Enable selection'),
				    'icon' => 'icon-power-off text-success',
			    ),
			    'disableSelection' => array(
				    'text' => $this->l('Disable selection'),
				    'icon' => 'icon-power-off text-danger',
			    ),
			    'divider' => array(
				    'text' => 'divider',
			    ),
		    ), $this->bulk_actions);
	    }

	    $helper = new HelperList();

	    // Empty list is ok
	    if (!is_array($this->_list)) {
		    $this->displayWarning($this->l('Bad SQL query', 'Helper') . '<br />' . htmlspecialchars($this->_list_error));

		    return false;
	    }

	    $this->setHelperDisplay($helper);
	    $helper->_default_pagination = $this->_default_pagination;
	    $helper->_pagination = $this->_pagination;
	    $helper->tpl_vars = $this->getTemplateListVars();
	    $helper->tpl_delete_link_vars = $this->tpl_delete_link_vars;

	    // For compatibility reasons, we have to check standard actions in class attributes
	    foreach ($this->actions_available as $action) {
		    if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action) {
			    $this->actions[] = $action;
		    }
	    }
	    $helper->is_cms = $this->is_cms;
	    $helper->sql = $this->_listsql;
	    foreach ($this->_list as $index=>$transaction){
	    	$this->_list[$index]['transaction_type'] = $this->translateTxType($this->_list[$index]['transaction_type']);
		    $this->_list[$index]['transaction_state'] = $this->translateTxState($this->_list[$index]['transaction_state']);
	    }
	    $list = $helper->generateList($this->_list, $this->fields_list);
	    return $list;

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
        $transactionPostProcessingService->process((float)$delta_amount);
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
