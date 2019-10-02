<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\BackendService;
use WirecardEE\Prestashop\Helper\PaymentProvider;
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
        $this->identifier = 'tx_id';

        $this->module = Module::getInstanceByName('wirecardpaymentgateway');

        $this->_defaultOrderBy = 'tx_id';
        $this->_defaultOrderWay = 'DESC';
        $this->_use_found_rows = true;

//        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
//        foreach ($statuses as $status) {
//            $this->statuses_array[$status['id_order_state']] = $status['name'];
//        }
//        $this->translator = $this->module->getTranslator();


        $this->fields_list = (new Transaction())->getFieldList();

        parent::__construct();
        $this->tpl_folder = 'backend/';
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
        try {
            $transaction_data = $this->mapTransactionDataToArray($this->object);
            $shop_config_service = new ShopConfigurationService($transaction_data['payment_method']);
            $payment_model = PaymentProvider::getPayment($transaction_data['payment_method']);
            $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
            $backend_service = new BackendService($payment_config, new WirecardLogger());

            $transaction = $payment_model->getTransactionInstance();
            $transaction->setParentTransactionId($transaction_data['id']);
            $possible_operations = (array)$backend_service->retrieveBackendOperations($transaction, true);

            // These variables are available in the Smarty context
            $this->tpl_view_vars = array(
                'current_index' => self::$currentIndex,
                'payment_method' => $payment_model->getName(),
                'possible_operations' => $this->formatOperations($possible_operations),
                'transaction' => $transaction_data,
            );

            return parent::renderView();
        } catch (\Throwable $e) {
            echo $e->getMessage() . " :: " . get_class($e);
        }
    }

    /**
     * THIS IS WORK IN PROGRESS.
     * DO NOT REVIEW AT THIS STAGE.
     *
     * @TODO Implement proper process workflow
     */
    public function postProcess()
    {
        $operation = \Tools::getValue('operation');
        $transaction_id = \Tools::getValue('transaction');

        if (!$operation || !$transaction_id) {
            return;
        }

        $transaction_data = new Transaction($transaction_id);
        $parent_transaction = $this->mapTransactionDataToArray($transaction_data);
        $shop_config_service = new ShopConfigurationService($parent_transaction['payment_method']);

        $payment_model = PaymentProvider::getPayment($parent_transaction['payment_method']);
        $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
        $backend_service = new BackendService($payment_config, new WirecardLogger());

        $transaction = $payment_model->getTransactionInstance();
        $transaction->setParentTransactionId($parent_transaction['id']);

        try {
            $response = $backend_service->process($transaction, $operation);
            $orders = \Order::getByReference($parent_transaction['order']);

            $response_factory = new ProcessablePaymentResponseFactory($response, $orders->getFirst(), ProcessablePaymentResponseFactory::PROCESS_BACKEND);
            $processing_strategy = $response_factory->getResponseProcessing();
            $processing_strategy->process();
        } catch (\Exception $e) {
            echo $e->getMessage() . " :: " . get_class($e);
        }
    }

    /**
     * Maps the database columns into an easily digestible array.
     *
     * @param $data
     * @return array
     * @since 2.4.0
     */
    protected function mapTransactionDataToArray($data)
    {
        if (!Validate::isLoadedObject($data)) {
            $this->errors[] = Tools::displayError($this->getTranslatedString('error_no_transaction'));
        }

        return array(
            'tx'             => $data->tx_id,
            'id'             => $data->transaction_id,
            'type'           => $data->transaction_type,
            'status'         => $data->transaction_state,
            'amount'         => $data->amount,
            'currency'       => $data->currency,
            'response'       => json_decode($data->response),
            'payment_method' => $data->paymentmethod,
            'order'          => $data->ordernumber,
            'badge'          => $data->transaction_state === 'open' ? 'green' : 'red',
        );
    }

    /**
     * Formats the post-processing operations for use in the template.
     *
     * @param $possible_operations
     * @return array
     * @since 2.4.0
     */
    protected function formatOperations($possible_operations)
    {
        $operations = [];

        // No operations are possible for this transaction.
        if ($possible_operations === false) {
            return $operations;
        }

        foreach (array_keys($possible_operations) as $operation) {
            $operations[] = [
                'action' => $operation,
                'text' => $this->getTranslatedString("text_{$operation}_transaction"),
            ];
        }

        return $operations;
    }
}
