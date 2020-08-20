<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Classes\Service;

use Wirecard\ExtensionOrderStateModule\Application\Mapper\GenericOrderStateMapper;
use Wirecard\ExtensionOrderStateModule\Application\Service\OrderState;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorablePostProcessingFailureException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\IgnorableStateException;
use Wirecard\ExtensionOrderStateModule\Domain\Exception\OrderStateInvalidArgumentException;
use WirecardEE\Prestashop\Classes\Config\OrderStateMappingDefinition;
use WirecardEE\Prestashop\Helper\Logger;
use WirecardEE\Prestashop\Helper\NumericHelper;
use WirecardEE\Prestashop\Helper\OrderStateTransferObject;

/**
 * Class OrderStateManagerService
 * @package WirecardEE\Prestashop\Classes\Service
 * @since 2.10.0
 */
class OrderStateManagerService implements ServiceInterface
{
    use NumericHelper;

    /**
     * @var OrderState
     */
    protected $service;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * OrderStateManagerService constructor.
     * @throws \Wirecard\ExtensionOrderStateModule\Domain\Exception\NotInRegistryException
     */
    public function __construct()
    {
        $orderStateMapper = new GenericOrderStateMapper(new OrderStateMappingDefinition());
        $this->service = new OrderState($orderStateMapper, $this->getPrecision());
        $this->logger = new Logger();
    }

    /**
     * @param int $currentOrderState
     * @param string $processType
     * @param array $transactionResponse
     * @param OrderAmountCalculatorService $orderAmountCalculator
     * @return null|string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function calculateNextOrderState(
        $currentOrderState,
        $processType,
        array $transactionResponse,
        OrderAmountCalculatorService $orderAmountCalculator
    ) {
        try {
            $input = new OrderStateTransferObject(
                $currentOrderState,
                $processType,
                $transactionResponse,
                $orderAmountCalculator
            );
            return $this->service->process($input);
        } catch (IgnorableStateException $e) {
            $this->logger->debug($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (OrderStateInvalidArgumentException $e) {
            $this->logger->emergency($e->getMessage(), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        } catch (IgnorablePostProcessingFailureException $e) {
            $this->logger->debug(get_class($e), ['exception_class' => get_class($e), 'method' => __METHOD__]);
        }
    }
}
