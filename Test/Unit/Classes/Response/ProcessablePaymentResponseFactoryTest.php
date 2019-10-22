<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Classes\Response\ProcessablePaymentResponseFactory;

class ProcessablePaymentResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $order;

    public function setUp()
    {
        $this->order = new \Order();
    }

    /**
     * @dataProvider provider
     * @param string $response_class_name
     * @param string $order_state
     * @param string $expected
     */
    public function testResponseFactory($response_class_name, $order_state, $expected)
    {
        /** @var \Wirecard\PaymentSdk\Response\Response $response */
        $response = $this->getMockBuilder($response_class_name)
                                ->disableOriginalConstructor()
                                ->getMock();

        $response_factory = new ProcessablePaymentResponseFactory(
            $response,
            $this->order,
            ProcessablePaymentResponseFactory::PROCESS_RESPONSE,
            $order_state
        );

        $actual = $response_factory->getResponseProcessing();

        $this->assertInstanceOf($expected, $actual);
    }

    public static function provider()
    {
        return array(
            array(
                \Wirecard\PaymentSdk\Response\SuccessResponse::class,
                'success',
                \WirecardEE\Prestashop\Classes\Response\Success::class
            ), array(
                \Wirecard\PaymentSdk\Response\FailureResponse::class,
                'failure',
                \WirecardEE\Prestashop\Classes\Response\Failure::class
            ), array(
                \Wirecard\PaymentSdk\Response\SuccessResponse::class,
                'cancel',
                \WirecardEE\Prestashop\Classes\Response\Cancel::class
            ), array(
                \Wirecard\PaymentSdk\Response\InteractionResponse::class,
                'success',
                \WirecardEE\Prestashop\Classes\Response\Redirect::class
            ), array(
                \Wirecard\PaymentSdk\Response\FormInteractionResponse::class,
                'success',
                \WirecardEE\Prestashop\Classes\Response\FormPost::class
            ), array(
                \WirecardEE\Prestashop\Models\Transaction::class,
                'success',
                \WirecardEE\Prestashop\Classes\Response\Failure::class
            )
        );
    }
}
