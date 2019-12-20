<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Service;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\Operation;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Classes\Finder\OrderFinder;
use WirecardEE\Prestashop\Classes\Finder\TransactionFinder;
use WirecardEE\Prestashop\Classes\ProcessType;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;
use WirecardEE\Prestashop\Classes\Transaction\Builder\PostProcessingTransactionBuilder;
use WirecardEE\Prestashop\Helper\NumericHelper;
use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use Exception;

/**
 * Class TransactionPostProcessingService
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Service
 */
class TransactionPostProcessingService implements ServiceInterface
{

    use NumericHelper;

    /** @var string */
    private $operation;
    /** @var int */
    private $transaction_id;
    /** @var array  */
    private $errors = [];

    /**
     * TransactionPostProcessingService constructor.
     * @param string $operation
     * @param int $transaction_id
     * @since 2.5.0
     */
    public function __construct($operation, $transaction_id)
    {
        $this->operation = $operation;
        $this->transaction_id = $transaction_id;
    }

    /**
     * @return array
     * @since 2.5.0
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Transaction postprocessing
     * @since 2.5.0
     */
    public function process($delta_amount)
    {

        try {
            $parentTransaction = (new TransactionFinder())->getTransactionById($this->transaction_id);
            if ($this->operation == Operation::CANCEL) {
                if(!$this->equals($delta_amount, $parentTransaction->getAmount())) {
                    $this->errors[] = "Cancellation is available only for the whole amount.";
                    return;
                }
            }
            if($delta_amount > $parentTransaction->getRemainingAmount()) {
                $remaining = $parentTransaction->getRemainingAmount();
                $amount = $parentTransaction->getAmount();
                $processed = $parentTransaction->getProcessedAmount();
                $this->errors[] = "Amount too large. $delta_amount > $remaining; amount: $amount, processed: $processed";
                return;
            }

            $postProcessingTransactionBuilder = new PostProcessingTransactionBuilder(
                PaymentProvider::getPayment($parentTransaction->getPaymentMethod()),
                $parentTransaction
            );

            $transaction = $postProcessingTransactionBuilder
                ->setOperation($this->operation)
                ->setDeltaAmount($delta_amount)
                ->build();

            $shop_config_service = new ShopConfigurationService($parentTransaction->getPaymentMethod());
            $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
            $backend_service = new BackendService($payment_config, new WirecardLogger());

            $response = $backend_service->process($transaction, $this->operation);
            $order = (new OrderFinder())->getOrderByReference($parentTransaction->getOrderNumber());

            $response_factory = new ProcessablePaymentResponseFactory(
                $response,
                $order,
                ProcessType::PROCESS_BACKEND
            );

            $processing_strategy = $response_factory->getResponseProcessing();
            $processing_strategy->process();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $logger = new WirecardLogger();
            $logger->error(
                'Error in class:' . __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $e->getMessage() . "(" . get_class($e) . ")"
            );
        }
    }
}
