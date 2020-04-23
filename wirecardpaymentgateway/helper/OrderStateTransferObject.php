<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

use Wirecard\ExtensionOrderStateModule\Domain\Contract\InputDataTransferObject;
use Wirecard\ExtensionOrderStateModule\Domain\Entity\Constant;

/**
 * Class OrderStateTransferObject
 * @package WirecardEE\Prestashop\Helper
 * @since 2.10.0
 */
class OrderStateTransferObject implements InputDataTransferObject
{
    const FIELD_PROCESS_TYPE = "process-type";
    const FIELD_TRANSACTION_TYPE = "transaction-type";
    const FIELD_TRANSACTION_STATE = "transaction-state";
    const FIELD_CURRENT_ORDER_STATE = "current-order-state";

    /**
     * @var string
     */
    private $processType = "";

    /**
     * @var string
     */
    private $transactionType = "";

    /**
     * @var string
     */
    private $transactionState = "";

    /**
     * @var int
     */
    private $currentOrderState = 0;

    /**
     * OrderStateTransferObject constructor.
     * @param $currentOrderState
     * @param $processType
     * @param array $transactionResponse
     */
    public function __construct($currentOrderState, $processType, array $transactionResponse)
    {

        $this->setCurrentOrderState($currentOrderState);
        $this->setProcessType($processType);
        $this->initFromTransactionResponse($transactionResponse);
    }

    /**
     * @return string
     */
    public function getProcessType()
    {
        return $this->processType;
    }

    /**
     * @param string $processType
     */
    public function setProcessType($processType)
    {
        $this->processType = (string) $processType;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @param string $transactionType
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     * @return string
     */
    public function getTransactionState()
    {
        return $this->transactionState;
    }

    /**
     * @param string $transactionState
     */
    public function setTransactionState($transactionState)
    {
        $this->transactionState = (string) $transactionState;
    }

    /**
     * @return int
     */
    public function getCurrentOrderState()
    {
        return $this->currentOrderState;
    }

    /**
     * @param int $currentOrderState
     */
    public function setCurrentOrderState($currentOrderState)
    {
        $this->currentOrderState = $currentOrderState;
    }

    /**
     * @param array $response
     * @return OrderStateTransferObject
     */
    public function initFromTransactionResponse(array $response)
    {
        if (isset($response[self::FIELD_TRANSACTION_TYPE])) {
            $this->setTransactionType($response[self::FIELD_TRANSACTION_TYPE]);
        }
        if (isset($response[self::FIELD_TRANSACTION_STATE])) {
            $this->setTransactionState($response[self::FIELD_TRANSACTION_STATE]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::FIELD_CURRENT_ORDER_STATE => $this->getCurrentOrderState(),
            self::FIELD_TRANSACTION_TYPE => $this->getTransactionType(),
            self::FIELD_TRANSACTION_STATE => $this->getTransactionState(),
            self::FIELD_PROCESS_TYPE => $this->getProcessType(),
        ];
    }
}
