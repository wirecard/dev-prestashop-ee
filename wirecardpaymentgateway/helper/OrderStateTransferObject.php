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
use WirecardEE\Prestashop\Classes\Service\OrderStateNumericalValues;

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
    const FIELD_REQUESTED_AMOUNT = "requested-amount";

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
     * @var OrderStateNumericalValues
     */
    private $numericalValues;

    /**
     * @var float
     */
    private $requestedAmount;

    /**
     * OrderStateTransferObject constructor.
     * @param $currentOrderState
     * @param $processType
     * @param array $transactionResponse
     * @param OrderStateNumericalValues $numericalValues
     */
    public function __construct(
        $currentOrderState,
        $processType,
        array $transactionResponse,
        OrderStateNumericalValues $numericalValues
    )
    {
        $this->validate($processType, $currentOrderState, $transactionResponse);
        $this->currentOrderState = $currentOrderState;
        $this->processType = $processType;
        $this->transactionState = $transactionResponse[self::FIELD_TRANSACTION_STATE];
        $this->transactionType = $transactionResponse[self::FIELD_TRANSACTION_TYPE];
        $this->numericalValues = $numericalValues;
        $this->requestedAmount = (float)$transactionResponse[self::FIELD_REQUESTED_AMOUNT];
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
     * @param $processType
     * @param $currentOrderState
     * @param $response
     */
    private function validate($processType, $currentOrderState, $response)
    {
        $mappingDefinition = new OrderStateMappingDefinition();
        $response[self::FIELD_PROCESS_TYPE] = $processType;
        $response[self::FIELD_CURRENT_ORDER_STATE] = $currentOrderState;
        $validationSpecs = [
            self::FIELD_TRANSACTION_TYPE => Constant::getTransactionTypes(),
            self::FIELD_TRANSACTION_STATE => Constant::getTransactionStates(),
            self::FIELD_PROCESS_TYPE => Constant::getProcessTypes(),
            self::FIELD_CURRENT_ORDER_STATE => array_keys($mappingDefinition->definitions()),
            self::FIELD_REQUESTED_AMOUNT => null,
        ];

        foreach ($validationSpecs as $fieldName => $validValues) {
            if (!isset($response[$fieldName])) {
                throw new InvalidArgumentException("Required field $fieldName is not set");
            }
            if (is_array($validValues)) {
                if (!in_array($response[$fieldName], $validValues)) {
                    throw new InvalidArgumentException("Field '$fieldName' is invalid");
                }
            }
        }
    }

    /**
     * @return float
     */
    public function getOrderOpenAmount()
    {
        return $this->numericalValues->getOrderOpenAmount();
    }

    /**
     * @return float
     */
    public function getTransactionRequestedAmount()
    {
        return $this->requestedAmount;
    }
}
