<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Constants;

/**
 * Class TxConstants
 * @since 2.10.0
 * @package WirecardEE\Prestashop\Classes\Constants
 */
class TxConstants
{

    /** @var string */
    const TRANSLATION_FILE = "txconstants";

    /** @var string */
    const TX_TYPE = 'TX_TYPE_';

    /** @var string */
    const TX_STATE = 'TX_STATE_';

    /** @var array */
    const TX_TYPE_KEYS = [
        'TX_TYPE_CHECK_ENROLLMENT' => 'tx_type_check_enrollment',
        'TX_TYPE_CHECK_PAYER_RESPONSE' => 'tx_type_check_payer_response',
        'TX_TYPE_AUTHORIZATION' => 'tx_type_authorization',
        'TX_TYPE_CAPTURE_AUTHORIZATION' => 'tx_type_capture_authorization',
        'TX_TYPE_REFUND_CAPTURE' => 'tx_type_refund_capture',
        'TX_TYPE_VOID_AUTHORIZATION' => 'tx_type_void_authorization',
        'TX_TYPE_VOID_CAPTURE' => 'tx_type_void_capture',
        'TX_TYPE_DEPOSIT' => 'tx_type_deposit',
        'TX_TYPE_PURCHASE' => 'tx_type_purchase',
        'TX_TYPE_DEBIT' => 'tx_type_debit',
        'TX_TYPE_REFUND_PURCHASE' => 'tx_type_refund_purchase',
        'TX_TYPE_REFUND_DEBIT' => 'tx_type_refund_debit',
        'TX_TYPE_DEBIT_RETURN' => 'tx_type_debit_return',
        'TX_TYPE_VOID_PURCHASE' => 'tx_type_void_purchase',
        'TX_TYPE_PENDING_DEBIT' => 'tx_type_pending_debit',
        'TX_TYPE_VOID_PENDING_DEBIT' => 'tx_type_void_pending_debit',
        'TX_TYPE_PENDING_CREDIT' => 'tx_type_pending_credit',
        'TX_TYPE_VOID_PENDING_CREDIT' => 'tx_type_void_pending_credit',
        'TX_TYPE_CREDIT' => 'tx_type_credit',
    ];
    const TX_STATE_KEYS = [
        'TX_STATE_CLOSED' => 'state_closed',
        'TX_STATE_OPEN' => 'state_open',
        'TX_STATE_SUCCESS' => 'state_success',
        'TX_STATE_AWAITING' => 'state_awaiting',
    ];
}
