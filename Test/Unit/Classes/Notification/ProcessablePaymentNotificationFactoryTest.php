<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Classes\Notification\ProcessablePaymentNotificationFactory;

class ProcessablePaymentNotificationFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $order;

    public function setUp()
    {
        $this->order = new \Order();
    }

    /**
     * @param string $response_class_name
     * @param boolean $add_payment_method
     * @param string $expected
     * @dataProvider provider
     */
    public function testGetPaymentProcessing($response_class_name, $add_payment_method, $expected)
    {
        /** @var \Wirecard\PaymentSdk\Response\Response $response */
        $response = $this->getMockBuilder($response_class_name)
                         ->disableOriginalConstructor()
                         ->getMock();

        if ($add_payment_method) {
            $response->method('getPaymentMethod')
                     ->willReturn('creditcard');
        }

        $notify_factory = new ProcessablePaymentNotificationFactory($this->order, $response);

        $this->assertInstanceOf($expected, $notify_factory->getPaymentProcessing());
    }

    public static function provider()
    {
        return array(
            array(
                \Wirecard\PaymentSdk\Response\SuccessResponse::class,
                true,
                \WirecardEE\Prestashop\Classes\Notification\Success::class
            ), array(
                \Wirecard\PaymentSdk\Response\FailureResponse::class,
                false,
                \WirecardEE\Prestashop\Classes\Notification\Failure::class
            ), array(
                \Wirecard\PaymentSdk\Response\InteractionResponse::class,
                false,
                \WirecardEE\Prestashop\Classes\Notification\Failure::class
            ),
        );
    }
}
