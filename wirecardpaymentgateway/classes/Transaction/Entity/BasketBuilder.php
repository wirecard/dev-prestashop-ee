<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Entity;

/**
 * Class BasketBuilder
 * @package WirecardEE\Prestashop\Classes\Transaction\Entity
 * @since 2.4.0
 */
class BasketBuilder implements EntityBuilderInterface
{
    /**
     * @param \Wirecard\PaymentSdk\Transaction\Transaction $transaction
     * @param \WirecardEE\Prestashop\Models\Transaction $parentTransactionData
     * @return void|\Wirecard\PaymentSdk\Transaction\Transaction
     */
    public function build($transaction, $parentTransactionData)
    {
        var_dump('build the basket for post processing');die();
        //@TODO implement the basket creation from transaction
    }
}
