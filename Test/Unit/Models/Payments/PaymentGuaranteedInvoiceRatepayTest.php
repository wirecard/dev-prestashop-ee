<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use WirecardEE\Prestashop\Models\PaymentGuaranteedInvoiceRatepay;
use WirecardEE\Prestashop\Helper\Service\ShopConfigurationService;

class PaymentGuaranteedInvoiceRatepayTest extends PHPUnit_Framework_TestCase
{
    private $payment;

    private $shopConfig;

    public function setUp()
    {
        $this->shopConfig = $this->createMock(ShopConfigurationService::class);
        $this->shopConfig->method('getField')
            ->will(
                $this->returnValueMap([
                    ['amount_min', 20],
                    ['amount_max', 350],
                    ['allowed_currencies', '["EUR"]'],
                    ['billingshipping_same', true],
                    ['shipping_countries', '["AT"]'],
                    ['billing_countries', '["AT"]']
                ])
            );

        $this->payment = new PaymentGuaranteedInvoiceRatepay();
        setProtectedProperty($this->payment, 'configuration', $this->shopConfig);
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Facture avec garantie de paiement par Crédit Agricole';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertInternalType('array', $actual);
    }

    public function testCreateTransaction()
    {
        /** @var Wirecard\PaymentSdk\Transaction\Transaction $actual */
        $actual = $this->payment->createTransaction();
        $this->assertInstanceOf(RatepayInvoiceTransaction::class, $actual);
    }

    public function testGetPostProcessingMandatoryEntities()
    {
        $expected = ['basket'];

        $this->assertEquals(
            $expected,
            $this->payment->getPostProcessingMandatoryEntities()
        );
    }

    /**
     * @dataProvider isAvailableProvider
     *
     * @param $isVirtualCart
     * @param $getOrderTotal
     * @param $expected
     */
    public function testRefactored($isVirtualCart, $getOrderTotal, $expected)
    {
        $cartMock = Mockery::mock(Cart::class);
        $cartMock->shouldReceive(
            [
                'getOrderTotal' => $getOrderTotal,
                'isVirtualCart' => $isVirtualCart
            ]
        );
        $cartMock->id_customer = 2;

        $paymentMock = Mockery::mock(PaymentGuaranteedInvoiceRatepay::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $paymentMock->shouldReceive('getCartFromContext')->andReturn($cartMock);
        setProtectedProperty($paymentMock, 'configuration', $this->shopConfig);

        $this->assertEquals($expected, $paymentMock->isAvailable());
    }

    public function isAvailableProvider()
    {
        return [
            /*[ isVirtualCart | getOrderTotal | $expected ]*/
            [false, 50, true],
            [true, 50, false],
            [false, 15, false]
        ];
    }
}
