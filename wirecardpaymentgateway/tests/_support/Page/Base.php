<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Page;

class Base
{

    /**
     * @var string
     * @since 1.3.4
     */
    protected $URL = '';

    /**
     * @var array
     * @since 1.3.4
     */
    protected $elements = array();

    /**
     * @var string
     * @since 1.3.4
     */
    protected $tester;

    //page specific text that can be found in the URL
    public $pageSpecific = '';

    /**
     * @var AcceptanceTester
     * @since 1.3.4
     */
    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    /**
     * Method getElement
     *
     * @param string $name
     * @return string
     *
     * @since 1.3.4
     */
    public function getElement($name)
    {
        return $this->elements[$name];
    }

    /**
     * Method getURL
     *
     * @return string
     *
     * @since 1.3.4
     */
    public function getURL()
    {
        return $this->URL;
    }

    /**
     * Method fillBillingDetails
     *
     * @since 1.3.4
     */
    public function fillBillingDetails()
    {
        ;
    }

    /**
     * Method fillCreditCardDetails
     *
     * @since 1.3.4
     */
    public function fillCreditCardDetails()
    {
        ;
    }

    /**
     * Method checkBox
     *
     * @param string $box
     * @since 1.3.4
     */
    public function checkBox($box)
    {
        $this->tester->checkOption($this->getElement($box));
    }

    /**
     * Method fillCustomerDetails
     *
     * @since 2.0.1
     */
    public function fillCustomerDetails()
    {
        ;
    }

    /**
     * Method performPaypalLogin
     *
     * @since   2.2.1
     */
    public function performPaypalLogin()
    {
        ;
    }

    /**
     * Method acceptCookies
     *
     * @since   4.0.1
     */
    public function acceptCookies()
    {
        ;
    }

    /**
     * Method getPageSpecific
     *
     * @return string
     */
    public function getPageSpecific()
    {
        return $this->pageSpecific;
    }
}
