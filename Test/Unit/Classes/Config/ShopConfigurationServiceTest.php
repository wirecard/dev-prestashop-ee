<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;
use WirecardEE\Prestashop\Models\PaymentCreditCard;
use WirecardEE\Prestashop\Models\PaymentSofort;

class ShopConfigurationServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var ShopConfigurationService */
    private $shopConfigService;

    public function setUp()
    {
        $this->shopConfigService = new ShopConfigurationService(PaymentCreditCard::TYPE);
    }

    public function testItReturnsTheCorrectFieldName()
    {
        $actual = $this->shopConfigService->getFieldName('secret');

        $this->assertEquals(
            'WIRECARD_PAYMENT_GATEWAY_CREDITCARD_SECRET',
            $actual
        );
    }

    public function testItReturnsTheCorrectFieldValue()
    {
        $actual = $this->shopConfigService->getField('payment_action');

        $this->assertEquals(
            'reserve',
            $actual
        );
    }

    public function testItUsesFallbackNamesForPrestaShop()
    {
        $sofortConfigService = new ShopConfigurationService(PaymentSofort::TYPE);

        $this->assertEquals($sofortConfigService->getType(), PaymentSofort::TYPE);
        $this->assertEquals(
            $sofortConfigService->getFieldName('secret'),
            'WIRECARD_PAYMENT_GATEWAY_SOFORT_SECRET'
        );
    }
}
