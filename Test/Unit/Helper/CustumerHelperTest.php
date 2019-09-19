<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use WirecardEE\Prestashop\Helper\CustomerHelper;

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
}
