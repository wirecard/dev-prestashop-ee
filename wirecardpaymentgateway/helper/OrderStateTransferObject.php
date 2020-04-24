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
use WirecardEE\Prestashop\Classes\Config\OrderStateMappingDefinition;
use InvalidArgumentException;

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
        $this->currentOrderState = $currentOrderState;
        $this->processType = $processType;
        $this->initFromTransactionResponse($transactionResponse);
        $this->validate();
    }

    /**
     * @return string
     */
    public function getProcessType()
    {
        return $this->processType;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @return string
     */
    public function getTransactionState()
    {
        return $this->transactionState;
    }

    /**
     * @return int
     */
    public function getCurrentOrderState()
    {
        return $this->currentOrderState;
    }

    /**
     * @param array $response
     * @return OrderStateTransferObject
     */
    public function initFromTransactionResponse(array $response)
    {
        if (isset($response[self::FIELD_TRANSACTION_TYPE])) {
            $this->transactionType = $response[self::FIELD_TRANSACTION_TYPE];
        }
        if (isset($response[self::FIELD_TRANSACTION_STATE])) {
            $this->transactionState = $response[self::FIELD_TRANSACTION_STATE];
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

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate()
    {
        $result = true;
        $mappingDefinition = new OrderStateMappingDefinition();
        if (!in_array($this->currentOrderState, array_keys($mappingDefinition->definitions()), true)) {
            throw new InvalidArgumentException("Order state '{$this->currentOrderState}' is invalid");
        }

        if (!in_array($this->transactionState, Constant::getTransactionStates(), true)) {
            throw new InvalidArgumentException("Transaction state '{$this->transactionState}' is invalid");
        }

        if (!in_array($this->transactionType, Constant::getTransactionTypes(), true)) {
            throw new InvalidArgumentException("Transaction type '$this->transactionType' is invalid");
        }

        if (!in_array($this->processType, Constant::getProcessTypes(), true)) {
            throw new InvalidArgumentException("Process type '$this->processType' is invalid");
        }

        return $result;
    }
}
