<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Page;

use \Codeception\Util\Locator;
use Exception as Exception;

class Checkout extends Base
{

    // include url of current page
    /**
     * @var string
     * @since 1.3.4
     */
    public $URL = 'index.php?controller=order';

    public $wirecard_frame = 'wirecard-integrated-payment-page-frame';
    /**
     * @var array
     * @since 1.3.4
     */

    public $elements = array(
        'Social title' => "//*[@name='id_gender']",
        'First Name' => "//*[@name='firstname']",
        'Last Name' => "//*[@name='lastname']",
        'Email' => "//*[@name='email']",
        'Address' => "//*[@name='address1']",
        'City' => "//*[@name='city']",
        'Zip/Postal Code' => "//*[@name='postcode']",
        'Phone' => "//*[@name='phone']",
        'Continue2' => "//*[@name='confirm-addresses']",
        'Continue3' => "//*[@name='confirmDeliveryOption']",
        'Wirecard Credit Card' => "//input[@type='radio'][contains(@data-module-name, 'wd-creditcard')]",
        'Wirecard PayPal' => "//input[@type='radio'][contains(@data-module-name, 'wd-paypal')]",
        'Place order' => "//*[@id='place_order']",

        'Credit Card First Name' => "//*[@id='pp-cc-first-name']",
        'Credit Card Last Name' => "//*[@id='pp-cc-last-name']",
        'Credit Card Card number' => "//*[@id='pp-cc-account-number']",
        'Credit Card CVV' => "//*[@id='pp-cc-cvv']",
        'Credit Card Valid until' => "//*[@id='pp-cc-expiration-date']",

        'I agree to the terms of service' => "//*[@name='conditions_to_approve[terms-and-conditions]']",
        "Order with an obligation to pay" => "//*[@class='btn btn-primary center-block']",

        'I agree to the terms and conditions and the privacy policy' => "//*[@name='psgdpr']",
        'Next' => "//*[@name='continue']"
    );

    /**
     * Method fillCustomerDetails
     *
     * @since 2.0.1
     */
    public function fillCustomerDetails()
    {
        $I = $this->tester;
        $data_field_values = $I->getDataFromDataFile('tests/_data/CustomerData.json');
        $I->selectOption($this->getElement('Social title'), '1');
        $I->waitForElementVisible($this->getElement('First Name'));
        $I->fillField($this->getElement('First Name'), $data_field_values->first_name);
        $I->waitForElementVisible($this->getElement('Last Name'));
        $I->fillField($this->getElement('Last Name'), $data_field_values->last_name);
        $I->waitForElementVisible($this->getElement('Email'));
        $I->fillField($this->getElement('Email'), $data_field_values->email_address);
    }

    /**
     * Method checkBox
     * @param string $box
     * @since 2.0.1
     */
    public function checkBox($box)
    {
        $I = $this->tester;
        try {
            $I->scrollTo(['class' => 'payment-options'], 20, 50);
            $I->checkOption($this->getElement($box));
        } catch (Exception $e) {
            $I->scrollTo(['name' => 'psgdpr'], 20, 50);
            $I->checkOption($this->getElement($box));
        }
    }

    /**
     * Method fillBillingDetails
     *
     * @since 2.0.1
     */
    public function fillBillingDetails()
    {
        $I = $this->tester;
        $data_field_values = $I->getDataFromDataFile('tests/_data/CustomerData.json');
        $I->waitForElementVisible($this->getElement('Address'));
        $I->fillField($this->getElement('Address'), $data_field_values->street_address);
        $I->waitForElementVisible($this->getElement('City'));
        $I->fillField($this->getElement('City'), $data_field_values->town);
        $I->waitForElementVisible($this->getElement('Zip/Postal Code'));
        $I->fillField($this->getElement('Zip/Postal Code'), $data_field_values->post_code);
        $I->waitForElementVisible($this->getElement('Phone'));
        $I->fillField($this->getElement('Phone'), $data_field_values->phone);
        $I->waitForElementVisible($this->getElement('Continue2'));
        $I->click($this->getElement('Continue2'));

        $I->waitForElementVisible($this->getElement('Continue3'));
        $I->click($this->getElement('Continue3'));
    }

    /**
     * Method fillCreditCardDetails
     * @since 1.3.4
     */
    public function fillCreditCardDetails()
    {
        $I = $this->tester;
        $data_field_values = $I->getDataFromDataFile('tests/_data/PaymentMethodData.json');

        $this->switchFrame();
        $I->waitForElementVisible($this->getElement('Credit Card Last Name'));
        $I->fillField($this->getElement('Credit Card First Name'), $data_field_values->creditcard->first_name);
        $I->fillField($this->getElement('Credit Card Last Name'), $data_field_values->creditcard->last_name);
        $I->fillField($this->getElement('Credit Card Card number'), $data_field_values->creditcard->card_number);
        $I->fillField($this->getElement('Credit Card CVV'), $data_field_values->creditcard->cvv);
        $I->fillField($this->getElement('Credit Card Valid until'), $data_field_values->creditcard->valid_until);
        $I->switchToIFrame();
    }

    /**
     * Method switchFrame
     * @since 1.3.4
     */
    public function switchFrame()
    {
        // Switch to Credit Card UI frame
        $I = $this->tester;
        //wait for Javascript to load iframe and it's contents
        $I->wait(2);
        //get wirecard seemless frame name
        $I->executeJS('jQuery("#' . $this->wirecard_frame . '").attr("name", "' . $this->wirecard_frame . '")');
        $I->switchToIFrame("$this->wirecard_frame");
    }
}
