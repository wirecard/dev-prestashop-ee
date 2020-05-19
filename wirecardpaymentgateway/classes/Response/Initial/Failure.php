<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Response\Initial;

use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;

class Failure extends \WirecardEE\Prestashop\Classes\Response\Failure
{
    /**
     * @throws \PrestaShopException
     */
    public function process()
    {
        $this->processForType(Constant::PROCESS_TYPE_INITIAL_RETURN);
    }
}
