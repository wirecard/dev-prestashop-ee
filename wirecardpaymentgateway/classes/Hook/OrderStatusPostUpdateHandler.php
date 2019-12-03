<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Hook;

use Wirecard\PaymentSdk\Transaction\Operation;
use WirecardEE\Prestashop\Classes\Finder\TransactionFinder;
use \WirecardEE\Prestashop\Classes\Config\Constants;
use Configuration;
use WirecardEE\Prestashop\Classes\Service\TransactionPossibleOperationService;
use WirecardEE\Prestashop\Classes\Service\TransactionPostProcessingService;
use PrestaShopDatabaseException;
use PrestaShopException;
use Exception;

/**
 * class OrderStatusPostUpdateHandler
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Classes\Hook
 */
class OrderStatusPostUpdateHandler implements CommandHandlerInterface
{
    /** @var int */
    private $changeToStatusId;
    /** @var OrderStatusPostUpdateCommand */
    private $command;

    public function __construct($changeToStatusId, OrderStatusPostUpdateCommand $command)
    {
        $this->command = $command;
        $this->changeToStatusId = intval($changeToStatusId);
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    protected function onChangeToShippingStatus()
    {
        // Check if status equals to "shipping" and automatic capture enabled in settings
        if ($this->command->getOrderState()->id == $this->changeToStatusId &&
            intval(Configuration::get(Constants::SETTING_GENERAL_AUTOMATIC_CAPTURE_ENABLED))) {
            // Find transaction
            $transaction = (new TransactionFinder())->getCurrentTransactionByOrderId($this->command->getOrderId());
            if ($transaction && $transaction->isTransactionStateOpen()) {
                $possibleOperationService = new TransactionPossibleOperationService($transaction);
                // Get possible payment operations for appropriate transaction
                $operations = $possibleOperationService->getPossibleOperationList(false);
                // if transaction accept operation "PAY"
                if (in_array(Operation::PAY, array_keys($operations))) {
                    // Init Transaction PostProcessing Service
                    $postProcessingService = new TransactionPostProcessingService(
                        Operation::PAY,
                        $transaction->getTxId()
                    );
                    // Process payment
                    $postProcessingService->process();
                }
            }
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handle()
    {
        // Must be moved to a separate class in case of expansion of logic
        $this->onChangeToShippingStatus();
    }
}
