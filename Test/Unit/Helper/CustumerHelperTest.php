<?php

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use WirecardEE\Prestashop\Helper\CustomerHelper;

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
class CustomerHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CustomerHelper $customerHelper
     */
    private $customerHelper;

    public function setUp()
    {
        $customer = new Customer(1);
        $orderId = 123;
        $challengeInd = ChallengeInd::NO_CHALLENGE;
        $tokenId = null;
        $this->customerHelper = new CustomerHelper($customer, $orderId, $challengeInd, $tokenId);
    }

    public function testGetChallengeIndicatorNoToken()
    {
        $actual = $this->customerHelper->getChallengeIndicator();
        $this->assertEquals(ChallengeInd::NO_CHALLENGE, $actual);
    }

    public function testGetChallengeIndicatorWithToken()
    {
        $customer = new Customer(1);
        $orderId = 123;
        $challengeInd = ChallengeInd::NO_CHALLENGE;
        $tokenId = '123-456-789-123';
        $customerHelper = new CustomerHelper($customer, $orderId, $challengeInd, $tokenId);
        $actual = $customerHelper->getChallengeIndicator();
        $this->assertEquals(ChallengeInd::CHALLENGE_MANDATE, $actual);
    }

    public function testGetChallengeIndicatorWithExistingToken()
    {
        $customer = new Customer(1);
        $orderId = 123;
        $challengeInd = ChallengeInd::NO_CHALLENGE;
        $tokenId = '123-456-789-111';
        $customerHelper = new CustomerHelper($customer, $orderId, $challengeInd, $tokenId);
        $actual = $customerHelper->getChallengeIndicator();
        $this->assertEquals(ChallengeInd::NO_CHALLENGE, $actual);
    }

    public function testGetAccountCreationDate()
    {
        $actual = $this->customerHelper->getAccountCreationDate();
        $this->assertEquals(new DateTime('2019-06-03 09:49:57'), $actual);
    }

    public function testGetAccountUpdateDate()
    {
        $actual = $this->customerHelper->getAccountUpdateDate();
        $this->assertEquals(new DateTime('2019-06-09 19:09:27'), $actual);
    }

    public function testGetAccountPassChangeDate()
    {
        $actual = $this->customerHelper->getAccountPassChangeDate();
        $this->assertEquals(new DateTime('2019-08-09 10:59:01'), $actual);
    }

    public function testGetAccountLastLogin()
    {
        $actual = $this->customerHelper->getAccountLastLogin();
        $this->assertEquals(gmdate('Y-m-d\TH:i:s\Z', strtotime('2019-08-04 02:37:40')), $actual);
    }

    public function testGetShippingAddressFirstUse()
    {
        $actual = $this->customerHelper->getShippingAddressFirstUse(1);
        $this->assertEquals(new DateTime('2019-08-09 10:59:01'), $actual);
    }

    public function testGetSuccessfulOrdersLastSixMonths()
    {
        $actual = $this->customerHelper->getSuccessfulOrdersLastSixMonths();
        $this->assertEquals(3, $actual);
    }

    public function testgetCardCreationDate()
    {
    }
}
