<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\PostProcessing;

use WirecardEE\Prestashop\Classes\Response\Success as SuccessAbstract;

class Success extends SuccessAbstract
{
    public function process()
    {
        parent::process();

        $transaction_id = \Tools::getValue('tx_id');

        $this->transaction_manager->markTransactionClosed($transaction_id);
        $this->context_service->setConfirmations(
            $this->getTranslatedString('success_new_transaction')
        );
    }
}