<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Entity;

use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Interface EntityBuilderInterface
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity
 * @since 2.4.0
 */
interface EntityBuilderInterface
{
    /**
     * @param Transaction $transaction
     * @param \WirecardEE\Prestashop\Models\Transaction $parentTransactionData
     * @return Transaction
     */
    public function build($transaction, $parentTransactionData);
}
