<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Models\Transaction;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\Operation;
use WirecardEE\Prestashop\Models\PaymentMasterpass;
use WirecardEE\Prestashop\Models\PaymentSepaCreditTransfer;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;

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
        $this->className = '\WirecardEE\Prestashop\Models\Transaction';
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
                'title' => $this->l('panel_transaction'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'transaction_id' => array(
                'title' => $this->l('panel_transcation_id'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'parent_transaction_id' => array(
                'title' => $this->l('panel_parent_transaction_id'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'amount' => array(
                'title' => $this->l('panel_amount'),
                'align' => 'text-right',
                'class' => 'fixed-width-xs',
                'type' => 'price',
            ),
            'currency' => array(
                'title' => $this->l('panel_currency'),
                'class' => 'fixed-width-xs',
                'align' => 'text-right',
            ),
            'ordernumber' => array(
                'title' => $this->l('panel_order_number'),
                'class' => 'fixed-width-lg',
            ),
            'cart_id' => array(
                'title' => $this->l('panel_cart_number'),
                'class' => 'fixed-width-lg',
            ),
            'paymentmethod' => array(
                'title' => $this->l('panel_payment_method'),
                'class' => 'fixed-width-lg',
            ),
            'transaction_type' => array(
                'title' => $this->l('transactionType'),
                'class' => 'fixed-width-xs',
            ),
            'transaction_state' => array(
                'title' => $this->l('transactionState'),
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
        if (!\Validate::isLoadedObject($this->object)) {
            $this->errors[] = \Tools::displayError($this->l('error_no_transaction'));
        }

        $transaction = $this->mapTransactionDataToArray();
        $payment = PaymentProvider::getPayment($transaction->paymentmethod);

        // These variables are available in the Smarty context
        $this->tpl_view_vars = array(
            'current_index' => self::$currentIndex,
            'payment_method' => $payment->getName(),
            'transaction' => $transaction,
        );

        return parent::renderView();
    }

    public function postProcess()
    {

    }

    protected function mapTransactionDataToArray()
    {
        return array(
            'id'        => $this->object->transaction_id,
            'type'      => $this->object->transaction_type,
            'status'    => $this->object->transaction_state,
            'amount'    => $this->object->amount,
            'currency'  => $this->object->currency,
            'response'  => json_decode($this->object->response),
        );
    }
}
