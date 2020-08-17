<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Models\CreditCardVault;

const USER_ID = 1;

class CreditCardVaultTestTest extends PHPUnit_Framework_TestCase
{

    private $vault;

    public function setUp()
    {
        $this->vault = new CreditCardVault(USER_ID);
    }

    public function testGetUserCards()
    {
        $this->assertEquals(new \DbQuery(), $this->vault->getUserCardsByAddressId(13));
    }

    public function testAddCard()
    {
        $this->assertEquals(null, $this->vault->addCard('123', '333', 13));
    }

    public function testDeleteCard()
    {
        $this->assertEquals(true, $this->vault->deleteCard('333'));
    }
}
