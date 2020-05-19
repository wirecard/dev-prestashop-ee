<?php


namespace WirecardEE\Prestashop\Classes\Notification\PostProcessing;


use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;
use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;

class Failure extends \WirecardEE\Prestashop\Classes\Notification\Failure implements ProcessablePaymentNotification
{

    /**
     * @since 2.1.0
     */
    public function process()
    {
        $this->processForType(Constant::PROCESS_TYPE_POST_PROCESSING_NOTIFICATION);
    }
}