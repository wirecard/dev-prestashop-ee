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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function handle()
    {
        $orderId = $this->command->getOrderId();
        $orderState = $this->command->getOrderState();

        if ($orderState->id == $this->changeToStatusId && intval(Configuration::get(Constants::SETTING_GENERAL_AUTOMATIC_CAPTURE_ENABLED))) {
            $transaction = (new TransactionFinder())->getCurrentTransactionByOrderId($orderId);
            if ($transaction && $transaction->isTransactionStateOpen()) {
                $operation = Operation::PAY;
                $possibleOperationService = new TransactionPossibleOperationService($transaction);
                $operations = $possibleOperationService->getPossibleOperationList();
                if (in_array($operation, array_keys($operations))) {
                    $postProcessingService = new TransactionPostProcessingService($operation, $transaction->getTxId());
                    $postProcessingService->process();
                }
            }
        }
    }
}