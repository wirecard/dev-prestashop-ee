<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Notification\PostProcessing;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use WirecardEE\Prestashop\Classes\Notification\Success as AbstractSuccess;

class Success extends AbstractSuccess
{
    /**
     * @inheritDoc
     */
    public function getOrderStateProcessType()
    {
        return Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION;
    }

    public function process()
    {
        parent::process();
        $this->orderAmountCalculator->markParentAsClosedOnFullAmount(
            $this->notification->getParentTransactionId()
        );
        $this->order_service->createOrderPayment();
    }
}
