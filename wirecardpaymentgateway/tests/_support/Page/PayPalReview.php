<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Page;

use Facebook\WebDriver\Exception\TimeOutException;

class PayPalReview extends Base
{

    /**
     * @var string
     * @since 2.2.1
     */
    public $URL = 'checkout';

    /**
     * @var array
     * @since 2.2.1
     */
    public $elements = array(
        'Pay Now' => "//*[@id='confirmButtonTop']",
        'Accept Cookies' => "//*[@id='acceptAllButton']",
        'Continue' => "//*[@class='btn full confirmButton continueButton']"
    );
    /**
     * Method performPaypalPayment
     *
     * @since   2.3.0
     */
    public function performPaypalPayment()
    {
        $I = $this->tester;
        try {
            $I->preparedClick($this->getElement('Pay Now'));
        } catch (TimeOutException $e) {
            $I->preparedClick($this->getElement('Continue'));
            $I->preparedClick($this->getElement('Pay Now'));
        }
    }


    /**
     * Method acceptCookies
     *
     * @since 2.6.1
     */
    public function acceptCookies()
    {
        $I = $this->tester;

        try {
            $I->waitForElement($this->getElement('Accept Cookies'), 15);
            $I->waitForElementVisible($this->getElement('Accept Cookies'), 15);
            $I->waitForElementClickable($this->getElement('Accept Cookies'), 60);
            $I->click($this->getElement('Accept Cookies'));
        } catch (NoSuchElementException $e) {
            $I->seeInCurrentUrl($this->getPageSpecific());
        }
    }

    /**
     * Method payNow
     *
     * @since 2.6.1
     */
    public function payNow()
    {
        $I = $this->tester;

        $I->wait(1);
        try {
            $I->waitForElementVisible($this->getElement('Pay Now'), 60); // secs
            $I->waitForElementClickable($this->getElement('Pay Now'), 60);
            $I->click($this->getElement('Pay Now'));
        } catch (NoSuchElementException $e) {
            $I->seeInCurrentUrl($this->getPageSpecific());
        }
    }
}
