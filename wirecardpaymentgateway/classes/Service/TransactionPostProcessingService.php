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
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Class TransactionPostProcessingService
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Service
 */
class TransactionPostProcessingService implements ServiceInterface
{

    use NumericHelper;
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "transactionpostprocessingservice";

    /** @var string */
    private $operation;
    /** @var int */
    private $transactionId;
    /** @var array  */
    private $errors = [];

    /**
     * TransactionPostProcessingService constructor.
     * @param string $operation
     * @param int $transactionId
     * @since 2.5.0
     */
    public function __construct($operation, $transactionId)
    {
        $this->operation = $operation;
        $this->transactionId = $transactionId;
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
     * @param $deltaAmount float
     * @since 2.5.0
     */
    public function process($deltaAmount)
    {

        try {
            $parentTransaction = (new TransactionFinder())->getTransactionByTxId($this->transactionId);
            if ($this->operation == Operation::CANCEL) {
                if (!$this->equals($deltaAmount, $parentTransaction->getAmount())) {
                    $this->errors[] = $this->getTranslatedString('postprocessing_error_cancellation');
                    return;
                }
            }
            if ($deltaAmount > $parentTransaction->getRemainingAmount()) {
                $this->errors[] = $this->getTranslatedString('postprocessing_error_amount');
                return;
            }

            $postProcessingTransactionBuilder = new PostProcessingTransactionBuilder(
                PaymentProvider::getPayment($parentTransaction->getPaymentMethod()),
                $parentTransaction
            );

            $transaction = $postProcessingTransactionBuilder
                ->setOperation($this->operation)
                ->setDeltaAmount($deltaAmount)
                ->build();

            $shop_config_service = new ShopConfigurationService($parentTransaction->getPaymentMethod());
            $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
            $backend_service = new BackendService($payment_config, new WirecardLogger());

            $response = $backend_service->process($transaction, $this->operation);
            $order = (new OrderFinder())->getOrderByReference($parentTransaction->getOrderNumber());

            $responseFactory = new ProcessablePaymentResponseFactory(
                $response,
                $order,
                ProcessType::PROCESS_BACKEND
            );

            $processingStrategy = $responseFactory->getResponseProcessing();
            $processingStrategy->process();
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
