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

/**
 * Class OrderStateTransferObject
 * @package WirecardEE\Prestashop\Helper
 * @since 2.10.0
 */
class OrderStateTransferObject implements InputDataTransferObject
{
    const FIELD_PROCESS_TYPE = "process_type";
    const FIELD_TRANSACTION_TYPE = "transaction_type";
    const FIELD_TRANSACTION_STATE = "transaction_state";
    const FIELD_CURRENT_ORDER_STATE = "current_order_state";

    /**
     * @var string
     */
    private $processType;

    /**
     * @var string
     */
    private $transactionType;

    /**
     * @var string
     */
    private $transactionState;

    /**
     * @var string
     */
    private $currentOrderState;

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
        $this->processType = $processType;
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
        $this->transactionState = $transactionState;
    }

    /**
     * @return string
     */
    public function getCurrentOrderState()
    {
        return $this->currentOrderState;
    }

    /**
     * @param string $currentOrderState
     */
    public function setCurrentOrderState($currentOrderState)
    {
        $this->currentOrderState = $currentOrderState;
    }

    /**
     * @param array $data
     * @return OrderStateTransferObject
     */
    public function initFromData(array $data)
    {
        if ($data[self::FIELD_PROCESS_TYPE]) {
            $this->setTransactionType($data[self::FIELD_PROCESS_TYPE]);
        }
        if ($data[self::FIELD_TRANSACTION_TYPE]) {
            $this->setProcessType($data[self::FIELD_TRANSACTION_TYPE]);
        }
        if ($data[self::FIELD_TRANSACTION_STATE]) {
            $this->setTransactionState($data[self::FIELD_TRANSACTION_STATE]);
        }
        if ($data[self::FIELD_CURRENT_ORDER_STATE]) {
            $this->setCurrentOrderState($data[self::FIELD_CURRENT_ORDER_STATE]);
        }

        return $this;
    }
}
