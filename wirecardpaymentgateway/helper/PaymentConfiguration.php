<?php

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;

class PaymentConfiguration {
    const CONFIG_TEMPLATE = 'WIRECARD_PAYMENT_GATEWAY_%s_%s';
    const FALLBACK_NAMES = [
        RatepayInvoiceTransaction::NAME => 'invoice',
        PoiPiaTransaction::NAME => 'poipia',
        SepaCreditTransferTransaction::NAME => 'sepacredittransfer',
        SofortTransaction::NAME => 'sofort',
    ];

    private $type;

    public function __construct($type) {
        $this->type = $type;
    }

    public function getField($field) {
        $databaseFieldName = $this->getFieldName($field);

        return \Configuration::get($databaseFieldName);
    }

    public function getFieldName($field)
    {
        $type = $this->getCurrentType();

        return sprintf(
            self::CONFIG_TEMPLATE,
            \Tools::strtoupper($type),
            \Tools::strtoupper($field)
        );
    }

    private function getCurrentType() {
        if (key_exists($this->type, self::FALLBACK_NAMES)) {
            return self::FALLBACK_NAMES[$this->type];
        }

        return $this->type;
    }
}