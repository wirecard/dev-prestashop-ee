<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
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
     * Ensures  compatibility with existing database keys.
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
