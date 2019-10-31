<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Service;

use http\Exception\InvalidArgumentException;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Classes\Finder\OrderFinder;
use WirecardEE\Prestashop\Classes\Finder\TransactionFinder;
use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;
use WirecardEE\Prestashop\Classes\Transaction\Builder\PostProcessingTransactionBuilder;
use WirecardEE\Prestashop\Helper\PaymentProvider;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;
use PrestaShopDatabaseException;
use PrestaShopException;
use WirecardEE\Prestashop\Models\Transaction;

class TransactionPossibleOperationService implements ServiceInterface
{
    /** @var Transaction */
    private $transaction;
    /** @var array */
    private $errors = [];

    /**
     * TransactionPossibleOperationService constructor.
     * @param Transaction $transaction
     * @throws \Exception
     */
    public function __construct($transaction)
    {
        if (!$transaction instanceof Transaction) {
            throw new \Exception("transaction isn't instance of Transaction");
        }
        $this->transaction = $transaction;
    }

    /**
     * @return array|bool
     */
    public function getPossibleOperationList()
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
        } catch (\Exception $exception) {
            $possible_operations = [];
        }

        return $possible_operations;
    }
}