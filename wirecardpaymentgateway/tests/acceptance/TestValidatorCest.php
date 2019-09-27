<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use \Codeception\Util\Locator;

class TestValidatorCest
{

    public function _before(\AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $email = getenv('PRESTA_VALIDATOR_USER');
        $password = getenv('PRESTA_VALIDATOR_PASSWORD');

        $I->submitForm('#login-box', [
            'email' => $email,
            'password' => $password
        ]);
        $I->see('Validate your module / theme');
    }

    /**
     * This test will be executed only in 'phpBrowser' environments
     *
     * @env validator
     * @group validator
     */

    public function tryToTest(AcceptanceTester $I)
    {
        $I->attachFile(Locator::find('input', ['type' => 'file']), getenv('PACKAGE'));
        $I->checkOption(Locator::find('input', ['type' => 'checkbox', 'name' => 'compatibility_1_7']));
        $I->click('Process the validation');
        $I->waitForElementNotVisible('//*[@id="warmup-frame"]', 60);
        $I->wait(80);
        $report = $I->grabPageSource();
        $fp = fopen(getenv('REPORT_FILE'), 'w');
        fwrite($fp, $report);
        fclose($fp);
    }
}
