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

use WirecardEE\Prestashop\Models\Transaction;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class WirecardTransactions
 *
 * @property WirecardPaymentGateway module
 * @since 1.0.0
 */
class WirecardTransactionsController extends ModuleAdminController
{
    private $logger;

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

        $this->_orderBy = 'tx_id';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->translator = $this->module->getTranslator();

        $this->fields_list = array(
            'tx_id' => array(
                'title' => $this->translator->trans('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'transaction_id' => array(
                'title' => $this->translator->trans('Transaction ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'parent_transaction_id' => array(
                'title' => $this->translator->trans('Parent Transaction ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'amount' => array(
                'title' => $this->translator->trans('Amount'),
                'align' => 'text-right',
                'class' => 'fixed-width-xs',
                'type' => 'price',
            ),
            'currency' => array(
                'title' => $this->translator->trans('Currency'),
                'class' => 'fixed-width-xs',
                'align' => 'text-right',
            ),

            'ordernumber' => array(
                'title' => $this->translator->trans('Order number'),
                'class' => 'fixed-width-lg',
            ),
            'paymentmethod' => array(
                'title' => $this->translator->trans('Payment method'),
                'class' => 'fixed-width-lg',
            ),
            'transaction_type' => array(
                'title' => $this->translator->trans('Transaction Type'),
                'class' => 'fixed-width-xs',
            ),
            'transaction_state' => array(
                'title' => $this->translator->trans('Transaction State'),
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
            $this->errors[] = \Tools::displayError('The transaction cannot be found within your database.');
        }

        $transaction = $this->object;
        /** @var \WirecardEE\Prestashop\Models\Payment $payment */
        $payment = $this->module->getPaymentFromType($transaction->paymentmethod);
        $response_data = json_decode($transaction->response);
        $currency = new Currency($transaction->currency);
        // Smarty assign
        $this->tpl_view_vars = array(
            'current_index' => self::$currentIndex,
            'test' => 'test',
            'transaction_id' => $transaction->transaction_id,
            'payment_method' => $transaction->paymentmethod,
            'transaction_type' => $transaction->transaction_type,
            'status' => $transaction->transaction_state,
            'amount' => $transaction->amount,
            'currency' => $currency->iso_code,
            'response_data' => $response_data,
            'canCancel' => $payment->can_cancel($transaction->transaction_type),
            'canCapture' => $payment->can_capture($transaction->transaction_type),
            'canRefund' => $payment->can_refund($transaction->transaction_type),
            'cancelLink' => $this->context->link->getAdminLink('WirecardTransactions', true, array(), array('action' => 'cancel', 'tx' => $transaction->tx_id))
        );

        return parent::renderView();
    }

    public function postProcess()
    {
        if (\Tools::getValue('action') && \Tools::getValue('tx')) {
            $transaction = new Transaction(\Tools::getValue('tx'));
            if (!Validate::isLoadedObject($transaction)) {
                $this->errors[] = Tools::displayError('The transcation cannot be found within your database.');
            }

            switch (\Tools::getValue('action')) {
                case 'cancel':
                    $this->cancelTransaction($transaction);
                    break;
            }
        }

        print_r('Not implemented yet');
    }

    /**
     * @param $transactionData
     */
    public function cancelTransaction($transactionData)
    {
        $paymentType = $transactionData->paymentmethod;
        $payment = $this->module->getPaymentFromType($paymentType);
        if ($payment) {
            $config = $payment->createPaymentConfig($this->module);
            $currency = new Currency($transactionData->currency);

            $transaction = $payment->createTransaction();
            $transaction->setParentTransactionId($transactionData->transaction_id);
            $transaction->setAmount(new \Wirecard\PaymentSdk\Entity\Amount($transactionData->amount, $currency->iso_code));
            $transactionService = new TransactionService($config, new WirecardLogger());
            try {
                /** @var $response \Wirecard\PaymentSdk\Response\Response */
                $response = $transactionService->process($transaction, 'cancel');
            } catch (\Exception $exception) {
                $logger = new WirecardLogger();
                $logger->error(__METHOD__ . ':' . $exception->getMessage());
            }

            if ( $response instanceof SuccessResponse ) {
                $order = new Order($transactionData->order_id);
                $order->setCurrentState((int) _PS_OS_CANCELED_);

                $transaction = Transaction::create(
                    $transactionData->order_id,
                    $transactionData->cart_id,
                    $transactionData->amount,
                    $currency->iso_code,
                    $response
                );
                if (! $transaction) {
                    $logger = new WirecardLogger();
                    $logger->error(__METHOD__ . 'Transaction could not be saved in transaction table');
                }
                //REDIRECT TO TRANSACTION PAGE
                var_dump($response);
                die();
            }
            if ( $response instanceof FailureResponse ) {
                $logger = new WirecardLogger();
                $logger->error(__METHOD__ . 'An error occured. The transaction could not be cancelled!');
            }
        }
    }
}
