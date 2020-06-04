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

/**
 * Class TxTranslationHelper
 * @package WirecardEE\Prestashop\Helper
 */
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
        $transactionType = TxConst::TX_TYPE . strtoupper(str_replace('-', '_', $transactionType));
        $translatedTxType = $this->getTranslatedString(TxConst::TX_TYPE_KEYS[$transactionType]);
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
        $transactionState = TxConst::TX_STATE. strtoupper($transactionState);
        $translatedTxState = $this->getTranslatedString(TxConst::TX_STATE_KEYS[$transactionState]);
        return $translatedTxState;
    }
}
