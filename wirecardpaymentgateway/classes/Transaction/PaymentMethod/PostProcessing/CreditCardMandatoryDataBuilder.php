<?php

namespace WirecardEE\Prestashop\Classes\Transaction\PaymentMethod\PostProcessing;

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use WirecardEE\Prestashop\Classes\Config\PaymentConfigurationFactory;
use WirecardEE\Prestashop\Classes\Transaction\TransactionBuilderInterface;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\PaymentCreditCard;

class CreditCardMandatoryDataBuilder implements TransactionBuilderInterface
{
    /**
     * @var CreditCardTransaction
     */
    private $transaction;

    /**
     * CreditCardMandatoryDataBuilder constructor.
     * @param CreditCardTransaction $transaction
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
        $this->shopConfigService = new ShopConfigurationService(PaymentCreditCard::TYPE);
        $this->paymentConfigFactory = new PaymentConfigurationFactory($this->shopConfigService);
    }

    public function build()
    {
        $config = $this->paymentConfigFactory->createConfig();

        $this->transaction->setConfig(
            $config->get(CreditCardTransaction::NAME)
        );

        return $this->transaction;
    }
}
