<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Service;

use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;

/**
 * Class ShopConfigurationService
 *
 * @package WirecardEE\Prestashop\Helper\Service
 * @since 2.1.0
 */
class ShopConfigurationService
{
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
        RatepayInvoiceTransaction::PAYMENT_NAME => 'invoice',
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
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get a configuration field from the database
     *
     * @param $field
     * @return string
     * @since 2.1.0
     */
    public function getField($field)
    {
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
        $type = $this->getPrestashopType();

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
    private function getPrestashopType()
    {
        if (key_exists($this->type, self::FALLBACK_NAMES)) {
            return self::FALLBACK_NAMES[$this->type];
        }

        return $this->type;
    }

    /**
     * Get the original type for use with the SDK
     *
     * @return string
     * @since 2.1.0
     */
    public function getType()
    {
        return $this->type;
    }
}
