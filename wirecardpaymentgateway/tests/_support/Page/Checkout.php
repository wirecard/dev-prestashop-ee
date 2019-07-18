<?php
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

namespace Page;

use \Codeception\Util\Locator;

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
//*[@id="customer-form"]/section/div[1]/div[1]/label[1]/span/input

    public $elements = array(
        'Social title' => "//*[@name='id_gender']",
        'First Name' => "//*[@name='firstname']",
        'Last Name' => "//*[@name='lastname']",
        'Email' => "//*[@name='email']",
        'Continue1' => "//*[@name='continue']",
        'Address' => "//*[@name='address1']",
        'City' => "//*[@name='city']",
        'Zip/Postal Code' => "//*[@name='postcode']",
        'Phone' => "//*[@name='phone']",
        'Continue2' => "//*[@name='confirm-addresses']",
        'Continue3' => "//*[@name='confirmDeliveryOption']",
        'Wirecard Credit Card' => '//*[@name="payment-option"]',

        'Credit Card First Name' => "//*[@id='pp-cc-first-name']",
        'Credit Card Last Name' => "//*[@id='pp-cc-last-name']",
        'Credit Card Card number' => "//*[@id='pp-cc-account-number']",
        'Credit Card CVV' => "//*[@id='pp-cc-cvv']",
        'Credit Card Valid until' => "//*[@id='pp-cc-expiration-date']",

        'I agree to the terms of service' => "//*[@name='conditions_to_approve[terms-and-conditions]']",
        "Order with an obligation to pay" => "//*[@class='btn btn-primary center-block']",
    );

    /**
     * Method fillBillingDetails
     *
     * @since 1.3.4
     */
    public function fillBillingDetails()
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
        $I->click($this->getElement('Continue1'));

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
        $data_field_values = $I->getDataFromDataFile('tests/_data/CardData.json');
        $I->selectOption($this->getElement('Wirecard Credit Card'), 'Wirecard Credit Card');

        $this->switchFrame();
        $I->waitForElementVisible($this->getElement('Credit Card Last Name'));
        $I->fillField($this->getElement('Credit Card First Name'), $data_field_values->first_name);
        $I->fillField($this->getElement('Credit Card Last Name'), $data_field_values->last_name);
        $I->fillField($this->getElement('Credit Card Card number'), $data_field_values->card_number);
        $I->fillField($this->getElement('Credit Card CVV'), $data_field_values->cvv);
        $I->fillField($this->getElement('Credit Card Valid until'), $data_field_values->valid_until);
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

    /**
     * Method checkBox
     * @param string $box
     * @since 1.3.4
     */
    public function checkBox($box)
    {
        $I = $this->tester;
        $I->scrollTo(['class' => 'payment-options'], 20, 50);
        $I->checkOption($this->getElement($box));
    }
}
