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
use WirecardEE\Prestashop\Classes\Transaction\PaymentMethod\PostProcessing\MandatoryDataBuilderFactory;
use WirecardEE\Prestashop\Models\Payment;
use WirecardEE\Prestashop\Models\Transaction as TransactionModel;

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

    public function __construct(Payment $paymentMethod, TransactionModel $transaction)
    {
        $this->paymentMethod = $paymentMethod;
        $this->transactionModel = $transaction;
    }

    /**
     * @return Transaction
     */
    public function build()
    {
        /** @var Transaction $transaction */
        $transaction = $this->paymentMethod->createTransactionInstance();
        $transaction = $this->addMandatoryData($transaction);

        $builderFactory = new MandatoryDataBuilderFactory($transaction);
        $paymentMethodBuilder = $builderFactory->create();

        $transaction = $paymentMethodBuilder->build();

        return $transaction;
    }

    private function addMandatoryData(Transaction $transaction)
    {
        //@TODO please make the gets nicer.
        $transaction->setAmount(
            new Amount(
                $this->transactionModel->get('amount'),
                $this->transactionModel->get('currency')
            )
        );
        $transaction->setParentTransactionId(
            $this->transactionModel->get('transaction_id')
        );

        return $transaction;
    }
}
