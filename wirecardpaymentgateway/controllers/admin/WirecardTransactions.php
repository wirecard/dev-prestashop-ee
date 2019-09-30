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

/**
 * Class WirecardTransactions
 *
 * @property WirecardPaymentGateway module
 * @since 1.0.0
 */
class WirecardTransactionsController extends ModuleAdminController
{
    use \WirecardEE\Prestashop\Helper\TranslationHelper;

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

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->translator = $this->module->getTranslator();

        $this->fields_list = array(
            'tx_id' => array(
                'title' => $this->getTranslatedString('panel_transaction'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'transaction_id' => array(
                'title' => $this->getTranslatedString('panel_transcation_id'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'parent_transaction_id' => array(
                'title' => $this->getTranslatedString('panel_parent_transaction_id'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'amount' => array(
                'title' => $this->getTranslatedString('panel_amount'),
                'align' => 'text-right',
                'class' => 'fixed-width-xs',
                'type' => 'price',
            ),
            'currency' => array(
                'title' => $this->getTranslatedString('panel_currency'),
                'class' => 'fixed-width-xs',
                'align' => 'text-right',
            ),
            'ordernumber' => array(
                'title' => $this->getTranslatedString('panel_order_number'),
                'class' => 'fixed-width-lg',
            ),
            'cart_id' => array(
                'title' => $this->getTranslatedString('panel_cart_number'),
                'class' => 'fixed-width-lg',
            ),
            'paymentmethod' => array(
                'title' => $this->getTranslatedString('panel_payment_method'),
                'class' => 'fixed-width-lg',
            ),
            'transaction_type' => array(
                'title' => $this->getTranslatedString('transactionType'),
                'class' => 'fixed-width-xs',
            ),
            'transaction_state' => array(
                'title' => $this->getTranslatedString('transactionState'),
                'class' => 'fixed-width-xs',
            ),

        );

        parent::__construct();
        $this->tpl_folder = 'backend/';
    }

    /**
     * Render detail transaction view
     *
     * @return mixed
     * @since 1.0.0
     */
    public function renderView()
    {
        $transaction = $this->mapTransactionDataToArray($this->object);
        $shop_config_service = new ShopConfigurationService($transaction['payment_method']);
        $payment_model = PaymentProvider::getPayment($transaction['payment_method']);
        $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
        $backend_service = new BackendService($payment_config, new WirecardLogger());

        // These variables are available in the Smarty context
        $this->tpl_view_vars = array(
            'current_index' => self::$currentIndex,
            'payment_method' => $payment_model->getName(),
            'possible_operations' => $backend_service->retrieveBackendOperations(),
            'transaction' => $transaction,
        );

        return parent::renderView();
    }

    public function postProcess()
    {
        $operation = \Tools::getValue('action');
        $transaction_id = \Tools::getValue('tx');

        if ($operation && $transaction_id) {
            $transaction_data = new Transaction();
            $parent_transaction = $this->mapTransactionDataToArray($transaction_data);
            $shop_config_service = new ShopConfigurationService($parent_transaction['payment_method']);

            $payment_model = PaymentProvider::getPayment($parent_transaction['payment_method']);
            $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
            $backend_service = new BackendService($payment_config, new WirecardLogger());

            $transaction = $payment_model->getTransactionInstance();
            $response = $backend_service->process($transaction, $operation);
        }
    }

    protected function mapTransactionDataToArray($data)
    {
        if (!Validate::isLoadedObject($data)) {
            $this->errors[] = Tools::displayError($this->getTranslatedString('error_no_transaction'));
        }

        return array(
            'id'             => $data->transaction_id,
            'type'           => $data->transaction_type,
            'status'         => $data->transaction_state,
            'amount'         => $data->amount,
            'currency'       => $data->currency,
            'response'       => json_decode($data->response),
            'payment_method' => $data->paymentmethod,
        );
    }
}
