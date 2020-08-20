<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Classes\Transaction\Builder\Entity;

use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Interface EntityBuilderInterface
 * @package WirecardEE\Prestashop\Classes\Transaction\Builder\Entity
 * @since 2.5.0
 */
interface EntityBuilderInterface
{
    /**
     * @param Transaction $transaction
     * @return Transaction
     * @since 2.5.0
     */
    public function build($transaction);
}
