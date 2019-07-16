<?php

use WirecardEE\Prestashop\Helper\CurrencyConverter;

class CurrencyConverterTest extends \PHPUnit_Framework_TestCase {

    /** @var CurrencyConverter */
    private $converter;

    public function setUp() {
        $this->converter = new CurrencyConverter();
    }

    public function testItConvertsTheCurrencyCorrectly() {
        $convertedAmount = $this->converter->convertToCurrency(50.0, 'USD');
        $this->assertEquals(25.0, $convertedAmount);
    }

    public function testItHandlesMissingCurrenciesGracefully() {
        $convertedAmount = $this->converter->convertToCurrency(250.0, 'PHP');
        $this->assertEquals(250.0, $convertedAmount);
    }
}