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

class PayPalLogIn extends Base {

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
		'Email' => "//*[@id='email']",
		'Password' => "//*[@id='password']",
		'Next' => "//*[@id='btnNext']",
		'Log In' => "//*[@id='btnLogin']",
        'Turn On One Touch' => "Turn On One Touch"
	);

	/**
	 * Method performPaypalLogin
	 *
	 * @since   2.2.1
	 */
	public function performPaypalLogin() 
	{
		$I = $this->tester;
		$data_field_values = $I->getDataFromDataFile( 'tests/_data/PaymentMethodData.json' );
		$I->preparedFillField($this->getElement( 'Email' ), $data_field_values->paypal->user_name);
		try 
		{
			$I->waitForElementVisible( $this->getElement( 'Password' ) );
		} 
		catch ( TimeOutException $e ) {
			$I->preparedClick( $this->getElement( 'Next' ) );
		}
		$I->preparedFillField( $this->getElement( 'Password' ), $data_field_values->paypal->password );
		$I->preparedClick( $this->getElement( 'Log In' ) );
        $I->wait(10);
        if (strpos($I->grabFromCurrentUrl(),"sighin?intent=checkout") != '') {
            //$I->waitForElementVisible( $this->getElement( 'Turn On One Touch' ) );
            $I->click( $this->getElement( 'Turn On One Touch' ) );
        }
	}
}
