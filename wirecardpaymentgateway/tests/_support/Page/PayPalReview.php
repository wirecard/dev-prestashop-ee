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
    public $URL = 'review';

    /**
     * @var array
     * @since 2.2.1
     */
    public $elements = array(
        'Pay Now' => "//*[@id='confirmButtonTop']",
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
}
