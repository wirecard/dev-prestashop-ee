<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes;

use Wirecard\PaymentSdk\Constant\ResponseMappingXmlFields;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Transaction\Transaction;

abstract class ProcessablePaymentFactory
{
    /**
     * @param Response $response
     * @return bool
     */
    protected function isPostProcessing($response)
    {
        $types = [
            Transaction::TYPE_CAPTURE_AUTHORIZATION,
            Transaction::TYPE_VOID_AUTHORIZATION,
            Transaction::TYPE_CREDIT,
            Transaction::TYPE_REFUND_CAPTURE,
            Transaction::TYPE_REFUND_DEBIT,
            Transaction::TYPE_REFUND_REQUEST,
            Transaction::TYPE_VOID_CAPTURE,
            Transaction::TYPE_REFUND_PURCHASE,
            Transaction::TYPE_REFERENCED_PURCHASE,
            Transaction::TYPE_VOID_PURCHASE,
            Transaction::TYPE_VOID_DEBIT,
            Transaction::TYPE_VOID_REFUND_CAPTURE,
            Transaction::TYPE_VOID_REFUND_PURCHASE,
            Transaction::TYPE_VOID_CREDIT,
        ];
        return in_array($response->getData()[ResponseMappingXmlFields::TRANSACTION_TYPE], $types);
    }
}
