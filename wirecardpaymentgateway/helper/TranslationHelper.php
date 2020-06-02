<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use WirecardEE\Prestashop\Classes\Constants\TxConstants as TxConst;

trait TranslationHelper
{
    /**
     * Overwritten translation function, used in the module
     *
     * @param string $key translation key
     * @param string $iso_lang
     * @param string|bool $specific filename of the translation key
     *
     * @return string translation
     * @since 2.0.0
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    protected function getTranslatedString($key, $iso_lang = 'en', $specific = false)
    {
        if (!$specific && defined("static::TRANSLATION_FILE")) {
            $specific = static::TRANSLATION_FILE;
        }

        if (!$specific) {
            $specific = \WirecardPaymentGateway::NAME;
        }

        $translation = \Translate::getModuleTranslation(
            \WirecardPaymentGateway::NAME,
            $key,
            $specific
        );

        if ($translation === $key) {
            $translation = \WirecardPaymentGateway::getTranslationForLanguage($iso_lang, $key, $specific);
        }

        return $translation;
    }

    /**
     * Translates the transaction types in the transaction table
     *
     * @param string $transactionType
     *
     * @return string
     * @since 2.10.0
     */
    protected function translateTxType($transactionType)
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
                $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS['tx_type_credit']);
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
    protected function translateTxState($transactionState)
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
