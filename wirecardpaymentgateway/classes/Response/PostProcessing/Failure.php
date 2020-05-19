<?php


namespace WirecardEE\Prestashop\Classes\Response\PostProcessing;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;

class Failure extends \WirecardEE\Prestashop\Classes\Response\Failure
{

    /**
     * @throws \PrestaShopException
     */
    public function process()
    {
        $this->processForType(Constant::PROCESS_TYPE_POST_PROCESSING_RETURN);
    }
}