<?php
require __DIR__ . '/../../../../wirecardpaymentgateway/models/Payments/PaymentPaypal.php';

class PaymentPaypalTest extends PHPUnit_Framework_TestCase
{
    private $payment;

    public function setUp()
    {
        $this->payment = new PaymentPaypal();
    }

    public function testName()
    {
        $actual = $this->payment->getName();

        $expected = 'Wirecard Payment Processing Gateway Paypal';

        $this->assertEquals($expected, $actual);
    }

    public function testFormFields()
    {
        $actual = $this->payment->getFormFields();
        $this->assertTrue(is_array($actual));
    }
}
