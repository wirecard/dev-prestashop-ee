<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\Transaction;
use WirecardEE\Prestashop\Classes\Transaction\Entity\EntityBuilderFactory;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Models\Transaction as TransactionModel;

/**
 * Class PostProcessingTransactionBuilder
 * @package WirecardEE\Prestashop\Classes\Transaction
 * @since 2.4.0
 */
class PostProcessingTransactionBuilder implements TransactionBuilderInterface
{
    /**
     * @var Payment
     */
    private $paymentMethod;

    /**
     * @var TransactionModel
     */
    private $transactionModel;

    /**
     * @var string
     */
    private $operation;

    /**
     * PostProcessingTransactionBuilder constructor.
     * @param Payment $paymentMethod
     * @param TransactionModel $transaction
     * @since 2.4.0
     */
    public function __construct(Payment $paymentMethod, TransactionModel $transaction)
    {
        $this->paymentMethod = $paymentMethod;
        $this->transactionModel = $transaction;
    }

    /**
     * Set the operation of the payment, needed for payment methods that use SEPA Credit
     *
     * @param $operation
     * @return $this
     * @since 2.4.0
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Builds the transaction
     *
     * @throws \Exception
     * @return Transaction
     * @since 2.4.0
     */
    public function build()
    {
        /** @var Transaction $transaction */
        $transaction = $this->paymentMethod->createTransactionInstance($this->operation);
        $transaction = $this->addPostProcessingMandatoryData($transaction);
        $transaction = $this->addPaymentMethodPostProcessingMandatoryData($transaction);

        return $transaction;
    }

    /**
     * Adds the generic post processing mandatory data(Amount, ParentTransactionId)
     *
     * @param Transaction $transaction
     * @return Transaction
     * @since 2.4.0
     */
    private function addPostProcessingMandatoryData($transaction)
    {
        $transaction->setAmount(
            new Amount(
                (float) $this->transactionModel->amount,
                $this->transactionModel->currency
            )
        );

        $transaction->setParentTransactionId(
            $this->transactionModel->transaction_id
        );

        return $transaction;
    }

    /**
     * Adds the payment method specific mandatory data to transaction
     *
     * @param Transaction $transaction
     * @throws \Exception
     * @return Transaction
     * @since 2.4.0
     */
    private function addPaymentMethodPostProcessingMandatoryData($transaction)
    {
        foreach ($this->paymentMethod->getPostProcessingMandatoryEntities() as $entity) {
            $entityBuilder = (new EntityBuilderFactory())->create($entity);
            $transaction = $entityBuilder->build($transaction, $this->transactionModel);
        }

        return $transaction;
    }
}
