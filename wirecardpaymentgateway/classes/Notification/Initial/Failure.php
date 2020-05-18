<?php


namespace WirecardEE\Prestashop\Classes\Notification\Initial;


use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotification;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;

class Failure extends \WirecardEE\Prestashop\Classes\Notification\Failure implements ProcessablePaymentNotification
{

    /**
     * @since 2.1.0
     */
    public function process()
    {
        $this->processForType(Constant::PROCESS_TYPE_INITIAL_NOTIFICATION);
    }
}