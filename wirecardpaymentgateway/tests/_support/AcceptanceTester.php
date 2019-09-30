<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */

use Helper\Acceptance;
use Helper\PhpBrowserAPITest;
use Page\Base;
use Page\Cart as CartPage;
use Page\Checkout as CheckoutPage;
use Page\PayPalLogIn as PayPalLogInPage;
use Page\PayPalReview as PayPalReviewPage;
use Page\Product as ProductPage;
use Page\Shop as ShopPage;
use Page\OrderReceived as OrderReceivedPage;
use Page\Verified as VerifiedPage;

class AcceptanceTester extends \Codeception\Actor
{

    use _generated\AcceptanceTesterActions;

    /**
     * @var string
     * @since 1.3.4
     */
    private $currentPage;

    /**
     * @var array
     * @since 2.2.1
     */
    private $mappedPaymentActions = [
        'creditcard' => [
            'config' => [
                'reserve' => 'reserve',
                'pay' => 'pay',
            ],
            'tx_table' => [
                'authorization' => 'authorization',
                'purchase' => 'purchase'
            ]
        ],
        'paypal' => [
            'config' => [
                'reserve' => 'reserve',
                'pay' => 'pay',
            ],
            'tx_table' => [
                'authorization' => 'authorization',
                'debit' => 'debit'
            ]
        ]
    ];

    /**
     * Method selectPage
     *
     * @param string $name
     * @return Base
     *
     * @since   1.3.4
     */
    private function selectPage($name)
    {
        switch ($name) {
            case 'Checkout':
                $page = new CheckoutPage($this);
                break;
            case 'Product':
                $page = new ProductPage($this);
                break;
            case 'Shop':
                $page = new ShopPage($this);
                break;
            case 'Verified':
                $this->wait(15);
                $page = new VerifiedPage($this);
                break;
            case 'Order Received':
                $this->wait(10);
                $page = new OrderReceivedPage($this);
                break;
            case 'Pay Pal Log In':
                $this->wait( 10 );
                $page = new PayPalLogInPage( $this );
                break;
            case 'Pay Pal Review':
                $this->wait( 20 );
                $page = new PayPalReviewPage( $this );
                break;
            default:
                $page = null;
        }
        return $page;
    }

    /**
     * Method getPageElement
     *
     * @param string $elementName
     * @return string
     *
     * @since   1.3.4
     */
    private function getPageElement($elementName)
    {
        //Takes the required element by it's name from required page
        return $this->currentPage->getElement($elementName);
    }

    /**
     * @Given I am on :page page
     * @since 1.3.4
     */
    public function iAmOnPage($page)
    {
        // Open the page and initialize required pageObject
        $this->currentPage = $this->selectPage($page);
        $this->amOnPage($this->currentPage->getURL());
    }

    /**
     * @When I click :object
     * @since 1.3.4
     */
    public function iClick($object)
    {
        $this->waitForElementVisible($this->getPageElement($object));
        $this->waitForElementClickable($this->getPageElement($object));
        $this->click($this->getPageElement($object));
    }

    /**
     * @When I am redirected to :page page
     * @since 1.3.4
     */
    public function iAmRedirectedToPage($page)
    {
        // Initialize required pageObject WITHOUT checking URL
        $this->currentPage = $this->selectPage($page);
        // Check only specific keyword that page URL should contain
        $this->seeInCurrentUrl($this->currentPage->getURL());
    }

    /**
     * @When I fill fields with :data
     * @since 1.3.4
     */
    public function iFillFieldsWith($data)
    {
        $this->fillFieldsWithData($data, $this->currentPage);
    }

    /**
     * @When I enter :fieldValue in field :fieldID
     * @since 1.3.4
     */
    public function iEnterInField($fieldValue, $fieldID)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID));
        $this->fillField($this->getPageElement($fieldID), $fieldValue);
    }

    /**
     * @Then I see :text
     * @since 1.3.4
     */
    public function iSee($text)
    {
        $this->see($text);
    }

    /**
     * @Given I prepare credit card checkout :type
     * @since 2.2.1
     */
    public function iPrepareCreditCardCheckout($type)
    {
        $this->prepareGenericCheckout($type);
    }

    /**
     * @Given I prepare checkout
     * @since 2.2.1
     */
    public function iPrepareCheckout()
    {
        $this->prepareGenericCheckout();
    }

    private function prepareGenericCheckout($type='')
    {
        $this->iAmOnPage('Product');
        $this->fillField($this->currentPage->getElement('Quantity'), '5');

        if (strpos($type, 'Non3DS') !== false) {
            $this->fillField($this->currentPage->getElement('Quantity'), '1');
        }
        $this->click($this->currentPage->getElement('Add to cart'));
        $this->waitForText('Product successfully added to your shopping cart');
    }

    /**
     * @When I check :box
     * @since 1.3.4
     */
    public function iCheck($box)
    {
        $this->currentPage->checkBox($box);
    }

    /**
     * @Given I login to Paypal
     * @since 2.2.1
     */
    public function iLoginToPaypal()
    {
        $this->currentPage->performPaypalLogin();
    }

    /**
     * @Given I select :paymentMethod
     * @param string $paymentMethod
     * @since 2.2.1
     */
    public function selectPaymentMethod($paymentMethod)
    {
        $this->selectOption($this->currentPage->getElement($paymentMethod), $paymentMethod);
    }

    /**
     * @Given I activate :paymentMethod payment action :paymentAction in configuration
     * @param string $paymentMethod
     * @param string $paymentAction
     * @since 2.0.1
     */
    public function iActivatePaymentActionInConfiguration($paymentMethod, $paymentAction)
    {
        $this->updateInDatabase(
            'ps_configuration',
            ['value' => $this->mappedPaymentActions[$paymentMethod]['config'][$paymentAction]],
            ['name' => 'WIRECARD_PAYMENT_GATEWAY_'.strtoupper($paymentMethod).'_PAYMENT_ACTION']
        );
    }

    /**
     * @Then I see :paymentMethod :paymentAction in transaction table
     * @param string $paymentMethod
     * @param string $paymentAction
     * @since 2.0.1
     */
    public function iSeeInTransactionTable($paymentMethod, $paymentAction)
    {
        # wait for transaction to appear in transaction table
        $this->wait(10);
        $this->seeInDatabase(
            'ps_wirecard_payment_gateway_tx',
            ['transaction_type' => $this->mappedPaymentActions[$paymentMethod]['tx_table'][$paymentAction]]
        );
        //check that last transaction in the table is the one under test
        $transactionTypes = $this->getColumnFromDatabaseNoCriteria('ps_wirecard_payment_gateway_tx', 'transaction_type');
        $this->assertEquals(end($transactionTypes), $this->mappedPaymentActions[$paymentMethod]['tx_table'][$paymentAction]);
    }
}
