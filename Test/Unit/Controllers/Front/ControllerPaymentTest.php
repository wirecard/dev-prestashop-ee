<?php
require_once __DIR__ . '/../../../../wirecardpaymentgateway/controllers/front/payment.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

class ControllerPaymentTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteTransaction()
    {
        $transaction = new PayPalTransaction();
        $transaction->setAmount(new Amount(2.00, 'EUR'));
        $transaction->setRedirect(new Redirect('http://test.com', 'http://test.com'));
        $transaction->setNotificationUrl('http://test.com');
        $config = new Config(
            'https://api-test.wirecard.com',
            '70000-APITEST-AP',
            'qD2wzQ_hrc!8'
        );
        $paymentConfig = new PaymentMethodConfig('paypal', '2a0e9351-24ed-4110-9a1b-fd0fee6bec26', 'dbc5a498-9a66-43b9-bf1d-a618dd399684');
        $config->add($paymentConfig);

        $paymentController = new WirecardPaymentGatewayPaymentModuleFrontController();
        $paymentController->setAmount(2.00);
        $paymentController->setCartId('123');
        $actual = $paymentController->postProcess();

        $this->assertTrue($actual);
    }
}
