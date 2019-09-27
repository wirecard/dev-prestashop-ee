<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Helper\CurrencyHelper;

class CurrencyConverterTest extends \PHPUnit_Framework_TestCase
{

    /** @var CurrencyHelper */
    private $converter;

    public function setUp()
    {
        $this->converter = new CurrencyHelper();
    }

    public function testItConvertsTheCurrencyCorrectly()
    {
        $convertedAmount = $this->converter->convertToCurrency(50.0, 'USD');
        $this->assertEquals(25.0, $convertedAmount);
    }

    public function testItHandlesMissingCurrenciesGracefully()
    {
        $convertedAmount = $this->converter->convertToCurrency(250.0, 'PHP');
        $this->assertEquals(250.0, $convertedAmount);
    }
}
