<?php


namespace WirecardEE\Prestashop\Helper;

use WirecardEE\Prestashop\Classes\Constants\TxConstants as TxConst;

class TxTranslationHelper
{

    use TranslationHelper;

    /** @var string */
    const TRANSLATION_FILE = "txconstants";

    /**
     * Translates the transaction types in the transaction table
     *
     * @param string $transactionType
     *
     * @return string
     * @since 2.10.0
     */
    public function translateTxType($transactionType)
    {
        $translatedTxType = '';
        switch ($transactionType) {
            case 'check-enrollment':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_CHECK_ENROLLMENT']);
                break;
            case 'check-payer-response':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_CHECK_PAYER_RESPONSE']);
                break;
            case 'authorization':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_AUTHOIRZATION']);
                break;
            case 'capture-authorization':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_CAPTURE_AUTHORIZATION']);
                break;
            case 'refund-capture':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_REFUND_CAPTURE']);
                break;
            case 'void-authorization':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_VOID_AUTHORIZATION']);
                break;
            case 'void-capture':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_VOID_CAPTURE']);
                break;
            case 'deposit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_DEPOSIT']);
                break;
            case 'purchase':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_PURCHASE']);
                break;
            case 'debit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_DEBIT']);
                break;
            case 'refund-purchase':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_REFUND_PURCHASE']);
                break;
            case 'refund-debit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_REFUND_DEBIT']);
                break;
            case 'debit-return':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_DEBIT_RETURN']);
                break;
            case 'void-purchase':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_VOID_PURCHASE']);
                break;
            case 'pending-debit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_PENDING_DEBIT']);
                break;
            case 'void-pending-debit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_VOID_PENDING_DEBIT']);
                break;
            case 'pending-credit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_PENDING_CREDIT']);
                break;
            case 'void-pending-credit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_VOID_PENDING_CREDIT']);
                break;
            case 'credit':
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['TX_TYPE_CREDIT']);
                break;
            default:
                break;
        }
        return $translatedTxType;
    }

    /**
     * Translates the transaction states in the transaction table
     *
     * @param string $transactionState
     *
     * @return string
     * @since 2.10.0
     */
    public function translateTxState($transactionState)
    {
        $translatedTxState = '';
        switch ($transactionState) {
            case 'closed':
                $translatedTxState = $this->getTranslatedString(TxConst::TX_STATE_KEYS['STATE_CLOSED']);
                break;
            case 'open':
                $translatedTxState = $this->getTranslatedString(TxConst::TX_STATE_KEYS['STATE_OPEN']);
                break;
            case 'success':
                $translatedTxState = $this->getTranslatedString(TxConst::TX_STATE_KEYS['STATE_SUCCESS']);
                break;
            case 'awaiting':
                $translatedTxState = $this->getTranslatedString(TxConst::TX_STATE_KEYS['STATE_AWAITING']);
                break;
            default:
                break;
        }
        return $translatedTxState;
    }
}
