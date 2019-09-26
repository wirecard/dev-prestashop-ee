<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

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
        if (!\Validate::isLoadedObject($this->object)) {
            $this->errors[] = \Tools::displayError($this->getTranslatedString('error_no_transaction'));
        }

        $transaction = $this->object;
        /** @var \WirecardEE\Prestashop\Models\Payment $payment */
        $payment = $this->module->getPaymentFromType($transaction->paymentmethod);
        $response_data = json_decode($transaction->response);
        // Smarty assign
        $this->tpl_view_vars = array(
            'current_index' => self::$currentIndex,
            'transaction_id' => $transaction->transaction_id,
            'payment_method' => $payment->getName(),
            'transaction_type' => $transaction->transaction_type,
            'status' => $transaction->transaction_state,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'response_data' => $response_data,
            'canCancel' => $payment->canCancel($transaction->transaction_type),
            'canCapture' => $payment->canCapture($transaction->transaction_type),
            'canRefund' => $payment->canRefund($transaction->transaction_type),
            'cancelLink' => $this->context->link->getAdminLink(
                'WirecardTransactions',
                true,
                array(),
                array('action' => 'cancel', 'tx' => $transaction->tx_id)
            ),
            'captureLink' => $this->context->link->getAdminLink(
                'WirecardTransactions',
                true,
                array(),
                array('action' => 'capture', 'tx' => $transaction->tx_id)
            ),
            'refundLink' => $this->context->link->getAdminLink(
                'WirecardTransactions',
                true,
                array(),
                array('action' => 'refund', 'tx' => $transaction->tx_id)
            ),
            'backButton' => $this->context->link->getAdminLink('WirecardTransactions', true)
        );

        return parent::renderView();
    }

    /**
     * Process the submit according to the action
     * @since 1.0.0
     */
    public function postProcess()
    {
        if (\Tools::getValue('action') && \Tools::getValue('tx')) {
            $transaction = $this->createTransaction(\Tools::getValue('tx'));
            if (!Validate::isLoadedObject($transaction)) {
                $this->errors[] = Tools::displayError($this->getTranslatedString('error_no_transaction'));
            }

            $this->handleTransaction($transaction, \Tools::getValue('action'));
        }

        parent::postProcess();
    }

    /**
     * Capture/Cancel/Refund Transaction
     *
     * @param $transactionData
     * @param string $operation
     * @since 1.0.0
     * @return mixed
     */
    public function handleTransaction($transactionData, $operation)
    {
        $paymentType = $transactionData->paymentmethod;

        if ($paymentType == 'creditcard') {
            $paymentType = $this->checkPaymentName($transactionData->order_id);
        }
        /** @var Payment $payment */
        $payment = $this->module->getPaymentFromType($paymentType);
        if ($payment) {
            $shopConfigService = new ShopConfigurationService($paymentType);
            $config = (new PaymentConfigurationFactory($shopConfigService))->createConfig();
            $operation = $this->getOperation($paymentType, $operation);
            switch ($operation) {
                case Operation::REFUND:
                    $transaction = $payment->createRefundTransaction($transactionData, $this->module);
                    if (in_array($paymentType, array('ideal', 'sofortbanking', 'sepadirectdebit'))) {
                        $shopConfigService = new ShopConfigurationService(PaymentSepaCreditTransfer::TYPE);
                        $config = (new PaymentConfigurationFactory($shopConfigService))->createConfig();
                        $operation = Operation::CREDIT;
                    }
                    if ($paymentType == 'ratepay-invoice') {
                        $operation = Operation::CANCEL;
                    }
                    break;
                case Operation::CANCEL:
                    $transaction = $payment->createCancelTransaction($transactionData);
                    break;
                case Operation::PAY:
                    $transaction = $payment->createPayTransaction($transactionData);
                    break;
            }
            $transactionService = new TransactionService($config, new WirecardLogger());
            try {
                /** @var $response \Wirecard\PaymentSdk\Response\Response */
                $response = $transactionService->process($transaction, $operation);
            } catch (\Exception $exception) {
                $logger = new WirecardLogger();
                $logger->error(__METHOD__ . ':' . $exception->getMessage());
                $this->errors[] = Tools::displayError($exception->getMessage());
            }

            if ($response instanceof SuccessResponse) {
                $db = \Db::getInstance();
                $where = 'transaction_id = "' . pSQL($transactionData->transaction_id) . '"';
                $db->update('wirecard_payment_gateway_tx', array(
                    'transaction_state' => 'closed'
                ), $where);

                $url = $this->context->link->getAdminLink(
                    'WirecardTransactions',
                    true,
                    array(),
                    array('tx_id' => $transactionData->tx_id)
                ). '&viewwirecard_payment_gateway_tx';
                Tools::redirectAdmin($url);
            } elseif ($response instanceof FailureResponse) {
                $errors = '';
                foreach ($response->getStatusCollection()->getIterator() as $item) {
                    /** @var Status $item */
                    $errors .= $item->getDescription() . "<br>\n";
                }
                $this->errors[] = $errors;
            }
        } else {
            $this->errors[] = \Tools::displayError($this->getTranslatedString('transaction_payment_not_found_error'));
        }
        return parent::postProcess();
    }

    /**
     * @param int $txId
     * @return Transaction
     */
    private function createTransaction($txId)
    {
        return new Transaction($txId);
    }

    /**
     * @param $paymentType
     * @param $operation
     * @return string
     */
    private function getOperation($paymentType, $operation)
    {
        if ($operation == 'capture') {
            return Operation::PAY;
        }

        if (in_array($paymentType, array(
                    'paypal',
                    'alipay-xborder',
                    'p24',
                    'masterpass'
                    ))
            && $operation == 'refund') {
            return Operation::CANCEL;
        }

        return $operation;
    }

    private function checkPaymentName($orderId)
    {
        $order = new Order($orderId);
        $masterpassConfig = new ShopConfigurationService(PaymentMasterpass::TYPE);

        switch ($order->payment) {
            case $masterpassConfig->getField('title'):
                return 'masterpass';
            default:
                return 'creditcard';
        }
    }
}
