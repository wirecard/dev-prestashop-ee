<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification\PostProcessing;

use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;
use WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;
use WirecardEE\Prestashop\Helper\OrderManager;

class Success extends AbstractSuccess implements ProcessablePaymentNotification
{
    public function process() {
        if ( OrderManager::isIgnorable( $this->notification ) ) {
        	return;
        }
        parent::process();
        try {
		    $parentTransaction = $this->getParentTransaction();
		    $parentTransaction->markSettledAsClosed();
		    $parentTransaction->updateOrder(
			    $this->order,
			    $this->notification,
			    $this->order_manager,
			    $this->order_service
		    );
        } catch ( \Exception $e ) {
            error_log( "\t\t\t" . __METHOD__ . ' ' . __LINE__ . ' ' . "exception: " . $e->getMessage() );
            throw $e;
        }
    }
}
