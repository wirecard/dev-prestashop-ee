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
use WirecardEE\Prestashop\Helper\Logger as WirecardLogger;

class Success extends AbstractSuccess implements ProcessablePaymentNotification
{
    /** @var WirecardLogger  */
    private $logger;

    /**
     * Success constructor.
     *
     * @since 2.7.0
     */
    public function __construct()
    {
    	$this->logger = new WirecardLogger();
    }

    public function process()
    {
        if (OrderManager::isIgnorable($this->notification)) {
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
        } catch (\Exception $exception) {
            $this->logger->error(
                'Error in class:'. __CLASS__ .
                ' method:' . __METHOD__ .
                ' exception: ' . $exception->getMessage()
            );
            throw $exception;
        }
    }
}
