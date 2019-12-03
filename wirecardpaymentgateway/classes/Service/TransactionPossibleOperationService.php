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

use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use WirecardEE\Prestashop\Helper\TranslationHelper;
use WirecardEE\Prestashop\Models\PaymentSepaCreditTransfer;
use WirecardEE\Prestashop\Models\Transaction;
use Exception;
use Tools;

/**
 * Class TransactionPossibleOperationService
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Service
 */
class TransactionPossibleOperationService implements ServiceInterface
{
    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "wirecardtransactions";

    /** @var Transaction */
    private $transaction;

    /**
     * TransactionPossibleOperationService constructor.
     * @param Transaction $transaction
     * @throws Exception
     */
    public function __construct($transaction)
    {
        if (!$transaction instanceof Transaction) {
            throw new Exception("transaction isn't instance of Transaction");
        }
        $this->transaction = $transaction;
    }

    /**
     * @param bool $format
     * @return array|bool
     */
    public function getPossibleOperationList($format = true)
    {
        $possible_operations = [];
        $shop_config_service = new ShopConfigurationService($this->transaction->getPaymentMethod());
        $payment_model = PaymentProvider::getPayment($this->transaction->getPaymentMethod());

        $payment_config = (new PaymentConfigurationFactory($shop_config_service))->createConfig();
        $backend_service = new BackendService($payment_config, new WirecardLogger());

        try {
            $transaction = $payment_model->createTransactionInstance();
            $transaction->setParentTransactionId($this->transaction->getTransactionId());
            $result = $backend_service->retrieveBackendOperations($transaction, true);
            if (is_array($result)) {
                $possible_operations = $result;
            }
        } catch (Exception $exception) {
            $possible_operations = [];
        }

        // We no longer support Masterpass
        if ($format && $this->transaction->getPaymentMethod() !== MasterpassTransaction::NAME) {
            $possible_operations = $this->formatOperations($possible_operations);
        }

        return $possible_operations;
    }

    /**
     * Formats the post-processing operations for use in the template.
     *
     * @param array $possible_operations
     * @return array
     * @since 2.4.0
     */
    private function formatOperations(array $possible_operations)
    {
        $sepaCreditConfig = new ShopConfigurationService(PaymentSepaCreditTransfer::TYPE);
        $operations = [];
        $translations = [
            //@TODO add constant to paymentSDK
            'capture' => $this->getTranslatedString('text_capture_transaction'),
            Operation::CANCEL => $this->getTranslatedString('text_cancel_transaction'),
            Operation::REFUND => $this->getTranslatedString('text_refund_transaction'),
        ];

        if ($possible_operations === false) {
            return $operations;
        }

        foreach ($possible_operations as $operation => $key) {
            if (!$sepaCreditConfig->getField('enabled') && $operation === Operation::CREDIT) {
                continue;
            }
            $translatable_key = Tools::strtolower($key);
            $operations[] = [
                "action" => $operation,
                "name" => $translations[$translatable_key]
            ];
        }

        return $operations;
    }
}
