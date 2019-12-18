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
use WirecardEE\Prestashop\Helper\Logger;
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
    const TRANSLATION_FILE = "transactionpossibleoperationservice";

    /** @var Transaction */
    private $transaction;

    /**
     * TransactionPossibleOperationService constructor.
     * @param Transaction $transaction
     * @throws Exception
     * @since 2.5.0
     */
    public function __construct($transaction)
    {
        if (!$transaction instanceof Transaction) {
            throw new Exception("transaction isn't instance of Transaction");
        }
        $this->transaction = $transaction;
    }

    /**
     * @return BackendService
     * @since 2.5.0
     */
    private function getBackendService()
    {
        return new BackendService($this->getPaymentConfig(), new WirecardLogger());
    }

    /**
     * @return ShopConfigurationService
     * @since 2.5.0
     */
    private function getShopConfigurationService()
    {
        return new ShopConfigurationService($this->transaction->getPaymentMethod());
    }

    /**
     * @return \Wirecard\PaymentSdk\Config\Config
     * @since 2.5.0
     */
    private function getPaymentConfig()
    {
        return (new PaymentConfigurationFactory($this->getShopConfigurationService()))->createConfig();
    }

    /**
     * @param bool $returnTemplateFormat
     * @return array|bool
     * @since 2.5.0
     */
    public function getPossibleOperationList($returnTemplateFormat = true)
    {
        $possible_operations = [];
        $payment_model = PaymentProvider::getPayment($this->transaction->getPaymentMethod());
        try {
            $transaction = $payment_model->createTransactionInstance();
            $transaction->setParentTransactionId($this->transaction->getTransactionId());
            $result = $this->getBackendService()->retrieveBackendOperations($transaction, true);
            if (is_array($result)) {
                $possible_operations = $result;
            }
        } catch (Exception $exception) {
            (new Logger())->error($exception->getMessage());
        }

        // We no longer support Masterpass
        if ($returnTemplateFormat && $this->transaction->getPaymentMethod() !== MasterpassTransaction::NAME) {
            $possible_operations = $this->getOperationsForTemplate($possible_operations);
        }

        return $possible_operations;
    }

    /**
     * Formats the post-processing operations for use in the template.
     *
     * @param array $possible_operations
     * @return array
     * @since 2.5.0
     */
    private function getOperationsForTemplate(array $possible_operations)
    {
        $sepaCreditConfig = new ShopConfigurationService(PaymentSepaCreditTransfer::TYPE);
        $operations = [];
        $translations = [
            //@TODO Add constant to paymentSDK
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

    /**
     * @param string $operation
     * @return bool
     * @throws Exception
     * @since 2.5.0
     */
    public function isOperationPossible($operation)
    {
        $operations = $this->getPossibleOperationList(false);
        return in_array($operation, array_keys($operations), true);
    }
}
