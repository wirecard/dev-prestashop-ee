<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Service;

use Wirecard\ExtensionOrderStateModule\Application\Mapper\GenericOrderStateMapper;
use Wirecard\ExtensionOrderStateModule\Application\Service\OrderState;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Config\OrderStateMappingDefinition;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\OrderStateTransferObject;
use WirecardEE\Prestashop\Helper\Service\ContextService;

/**
 * Class OrderStateManagerService
 * @package WirecardEE\Prestashop\Classes\Service
 * @since 2.10.0
 */
class OrderStateManagerService implements ServiceInterface
{
    /**
     * @var OrderState
     */
    protected $service;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ContextService
     */
    private $context_service;

    /**
     * OrderStateManagerService constructor.
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    public function __construct()
    {
        $orderStateMapper = new GenericOrderStateMapper(new OrderStateMappingDefinition());
        $this->service = new OrderState($orderStateMapper);
    }

    /**
     * @param int $currentOrderState
     * @param string $processType
     * @param array $transactionResponse
     * @param OrderAmountCalculatorService $orderAmountCalculator
     * @return int|mixed|string
     * @throws IgnorablePostProcessingFailureException
     * @throws IgnorableStateException
     * @throws OrderStateInvalidArgumentException
     */
    public function calculateNextOrderState(
        $currentOrderState,
        $processType,
        array $transactionResponse,
        OrderAmountCalculatorService $orderAmountCalculator
    )
    {
        $input = new OrderStateTransferObject(
            $currentOrderState,
            $processType,
            $transactionResponse,
            $orderAmountCalculator
        );
        $nextState = $this->service->process($input);
        (new Logger())->debug(print_r($input->toArray(), true), ['method' => __METHOD__, 'line' => __LINE__, 'nextState' => $nextState]);
        return $nextState;
    }

    private function processBackend()
    {
        $errors = $this->getErrorsFromStatusCollection($this->response->getStatusCollection());
        $this->context_service->setErrors(\Tools::displayError(implode('<br>', $errors)));
    }

}
