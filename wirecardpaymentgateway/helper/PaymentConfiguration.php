<?php

namespace WirecardEE\Prestashop\Helper;

use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;

/**
 * Class PaymentConfiguration
 *
 * @package WirecardEE\Prestashop\Helper
 * @since 2.1.0
 */
class PaymentConfiguration {
    /**
     * @var string
     * @since 2.1.0
     */
    const CONFIG_TEMPLATE = 'WIRECARD_PAYMENT_GATEWAY_%s_%s';

    /**
     * Ensures compatibility with existing database keys.
     *
     * @var array
     * @string 2.1.0
     */
    const FALLBACK_NAMES = [
        RatepayInvoiceTransaction::NAME => 'invoice',
        PoiPiaTransaction::NAME => 'poipia',
        SepaCreditTransferTransaction::NAME => 'sepacredittransfer',
        SofortTransaction::NAME => 'sofort',
    ];

    /**
     * @var string
     * @since 2.1.0
     */
    private $type;

    /**
     * PaymentConfiguration constructor.
     *
     * @param $type
     * @since 2.1.0
     */
    public function __construct($type) {
        $this->type = $type;
    }

    /**
     * Get a configuration field from the database
     *
     * @param $field
     * @return string
     * @since 2.1.0
     */
    public function getField($field) {
        $databaseFieldName = $this->getFieldName($field);

        return \Configuration::get($databaseFieldName);
    }

    /**
     * Get the key for a configuration field
     *
     * @param $field
     * @return string
     * @since 2.1.0
     */
    public function getFieldName($field)
    {
        $type = $this->getCurrentType();

        return sprintf(
            self::CONFIG_TEMPLATE,
            \Tools::strtoupper($type),
            \Tools::strtoupper($field)
        );
    }

    /**
     * Get the fallback name for a type or just return the type.
     *
     * @return string
     * @since 2.1.0
     */
    private function getCurrentType() {
        if (key_exists($this->type, self::FALLBACK_NAMES)) {
            return self::FALLBACK_NAMES[$this->type];
        }

        return $this->type;
    }
}