<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use WirecardEE\Prestashop\Classes\Transaction\Builder\PostProcessingTransactionBuilder;
use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ContextService;
use WirecardEE\Prestashop\Models\PaymentSepaCreditTransfer;
use WirecardEE\Prestashop\Models\Transaction;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;

/**
 * Class WirecardTransactions
 *
 * @property WirecardPaymentGateway module
 * @since 1.0.0
 */
class WirecardTransactionsController extends ModuleAdminController
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardtransactions";

    const UNINITIALIZED_PARTIAL_PROCESSING = -1;

    /** @var ContextService */
    protected $context_service;

    private $processed_amount = self::UNINITIALIZED_PARTIAL_PROCESSING;

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

        parent::__construct();
        $this->tpl_folder = 'backend/';
    }

    private function hasProcessedAmount()
    {
        return $this->processed_amount != self::UNINITIALIZED_PARTIAL_PROCESSING;
    }

    /**
     * Render detail transaction view
     *
     * @return mixed
     * @since 2.4.0 Major refactoring and simplification
     * @since 1.0.0
     */
    public function renderView()
    {
        $this->validateTransaction($this->object);
        $transactionModel = new Transaction($this->object->tx_id);
        $transaction_data = $this->mapTransactionDataToArray($transactionModel);

        $shop_config_service = new ShopConfigurationService($transaction_data['payment_method']);
        $payment_model = PaymentProvider::getPayment($transaction_data['payment_method']);

        $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
        $backend_service = new BackendService($payment_config, new WirecardLogger());

        try {
            $transaction = $payment_model->createTransactionInstance();
            $transaction->setParentTransactionId($transaction_data['id']);
            $possible_operations = $backend_service->retrieveBackendOperations($transaction, true);
        } catch (\Exception $exception) {
            $this->errors[] = \Tools::displayError(
                $exception->getMessage()
            );
            return parent::renderView();
        }

        // We no longer support Masterpass
        $operations = $transaction_data['payment_method'] === MasterpassTransaction::NAME
            ? []
            : $this->formatOperations($possible_operations, $transactionModel);


        $transaction_amount = $transactionModel->getAmount();
        $processed_amount = $transactionModel->getProcessedAmount();
        if($this->hasProcessedAmount()) {
            $processed_amount = $this->processed_amount;
        }
        $remaining_delta_amount = $transaction_amount - $processed_amount;

        $child_transactions = $transactionModel->getAllChildTransactions();
        // These variables are available in the Smarty context
        $amounts = compact('remaining_delta_amount', 'transaction_amount', 'processed_amount');
        $this->tpl_view_vars = array(
            'current_index' => self::$currentIndex,
            'back_link' => (new Link())->getAdminLink('WirecardTransactions', true),
            'payment_method' => $payment_model->getName(),
            'possible_operations' => $operations,
            'transaction' => $transaction_data,
            //'child_transactions' => $child_transactions,
            //'amounts' => $amounts,
            'remaining_delta_amount' => $remaining_delta_amount,
        );

        return parent::renderView();
    }

    /**
     * Process follow-up actions such as refund/cancel/etc
     *
     * @since 2.4.0 Major refactoring
     * @since 1.0.0
     */
    public function postProcess()
    {
        $operation = \Tools::getValue('operation');
        $transaction_id = \Tools::getValue('transaction');

        // This prevents the function from running on the list page
        if (!$operation || !$transaction_id) {
            return parent::postProcess();
        }
        $delta_amount = \Tools::getValue('partial-delta-amount');

        $parentTransaction = new Transaction($transaction_id);
        $this->object = $parentTransaction;
        $postProcessingTransactionBuilder = new PostProcessingTransactionBuilder(
            PaymentProvider::getPayment($parentTransaction->getPaymentMethod()),
            $parentTransaction
        );

        try {
            $transaction = $postProcessingTransactionBuilder
                ->setOperation($operation)
                ->setDeltaAmount($delta_amount)
                ->build();

            $shop_config_service = new ShopConfigurationService($parentTransaction->getPaymentMethod());
            $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
            $backend_service = new BackendService($payment_config, new WirecardLogger());

            $response = $backend_service->process($transaction, $operation);
            $orders = \Order::getByReference($parentTransaction->getOrderNumber());

            $response_factory = new ProcessablePaymentResponseFactory(
                $response,
                $orders->getFirst(),
                ProcessablePaymentResponseFactory::PROCESS_BACKEND
            );

            $processing_strategy = $response_factory->getResponseProcessing();
            $processing_strategy->process();
            $this->processed_amount = $parentTransaction->getProcessedAmount();
            $parentTransaction->clearCache();
        } catch (\Exception $e) {
            $this->errors[] = \Tools::displayError(
                $e->getMessage()
            );

            $logger = new WirecardLogger();
            $logger->error(
                'Error in class:'. __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $e->getMessage() . "(" . get_class($e) . ")"
            );
        }

        return parent::postProcess();
    }

    /**
     * Maps the database columns into an easily digestible array.
     *
     * @param object $data
     * @return array
     * @since 2.4.0
     */
    private function mapTransactionDataToArray(Transaction $data)
    {
        return array(
            'tx'             => $data->getTxId(),
            'id'             => $data->getTransactionId(),
            'type'           => $data->getTransactionType(),
            'status'         => $data->getTransactionState(),
            'amount'         => $data->getAmount(),
            'currency'       => $data->getCurrency(),
            'response'       => json_decode($data->getResponse()),
            'payment_method' => $data->getPaymentMethod(),
            'order'          => $data->getOrderNumber(),
            'badge'          => $data->getTransactionState() === 'open' ? 'green' : 'red',
        );
    }

    /**
     * Checks that the transaction data is a valid object from the PrestaShop
     * database and adds an error if this is not the case.
     *
     * @param object $data
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

    /**
     * Formats the post-processing operations for use in the template.
     *
     * @param $possible_operations
     * @return array
     * @since 2.4.0
     */
    private function formatOperations($possible_operations, Transaction $transaction)
    {
        $sepaCreditConfig = new ShopConfigurationService(PaymentSepaCreditTransfer::TYPE);
        $operations = [];
        $translations = [
            //@TODO add constant to paymentSDK once TPWDCEE-5672 is implemented
            'capture' => $this->getTranslatedString('text_capture_transaction'),
            Operation::CANCEL => $this->getTranslatedString('text_cancel_transaction'),
            Operation::REFUND => $this->getTranslatedString('text_refund_transaction'),
        ];

        if ($possible_operations === false) {
            return $operations;
        }

        //we cannot cancel after making partial refunds
        if($transaction->getProcessedAmount() > 0) {
            unset($possible_operations[Operation::CANCEL]);
        }

        foreach ($possible_operations as $operation => $key) {
            if (!$sepaCreditConfig->getField('enabled') && $operation === Operation::CREDIT) {
                continue;
            }
            $translatable_key = \Tools::strtolower($key);
            $operations[] = [
                "action" => $operation,
                "name" => $translations[$translatable_key]
            ];
        }

        return $operations;
    }
}
