<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Hook;


use \PrestaShopDatabaseException;
use \PrestaShopException;
use \WirecardPaymentGateway;
use WirecardEE\Prestashop\Helper\OrderManager;

/**
 * class BeforeOrderStatusUpdateHandler
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Hook
 */
class AfterOrderStatusUpdateHandler implements CommandHandlerInterface
{
    /** @var OrderStatusUpdateCommand */
    private $command;
    /**
     * @var WirecardPaymentGateway
     */
    private $module;

    /**
     * OrderStatusPostUpdateHandler constructor.
     * @param OrderStatusUpdateCommand $command
     * @param WirecardPaymentGateway $module
     * @since 2.5.0
     */
    public function __construct(OrderStatusUpdateCommand $command, WirecardPaymentGateway $module)
    {
        $this->command = $command;
        $this->module = $module;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.5.0
     */
    public function handle()
    {
        $this->onPartiallyCapture();
        $this->onPartiallyRefund();
    }


    private function onPartiallyCapture()
    {
        if ($this->command->getOrderState()->id == \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_CAPTURED)) {

        }
    }

    private function onPartiallyRefund()
    {
        if ($this->command->getOrderState()->id == \Configuration::get(OrderManager::WIRECARD_OS_PARTIALLY_REFUNDED)) {
            error_log("REFUND");
        }
    }
}
