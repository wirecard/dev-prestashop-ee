<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Test\Helper\Form\Element;

use WirecardEE\Prestashop\Classes\Constants\FormConstants;
use WirecardEE\Prestashop\Helper\Form\Element\SubmitButton;

/**
 * Class SubmitButtonTest
 * @package WirecardEE\Test\Prestashop\Helper\Form\Element
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\Form\Element\SubmitButton
 */
class SubmitButtonTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DEFAULT_TYPE = "default";
    const DEFAULT_ARG_NAME = "foo";
    const DEFAULT_ARG_LABEL = "bar";

    /** @var SubmitButton */
    protected $submitButton;

    protected function setUp()
    {
        $this->submitButton = new SubmitButton(self::DEFAULT_ARG_NAME, self::DEFAULT_ARG_LABEL);
    }

    /**
     * @group unit
     * @small
     * @covers ::getType
     */
    public function testGetType()
    {
        $this->assertEquals(FormConstants::FORM_ELEMENT_TYPE_SUBMIT, $this->submitButton->getType());
    }

    /**
     * @group unit
     * @small
     * @covers ::getGroup
     */
    public function testGetGroup()
    {
        $this->assertEquals(FormConstants::FORM_GROUP_TYPE_SUBMIT, $this->submitButton->getGroup());
    }

    /**
     * @group unit
     * @small
     * @covers ::build
     */
    public function testBuild()
    {
        $result = $this->submitButton->build();
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $expectedResult = [
            'name' => self::DEFAULT_ARG_NAME,
            'title' => self::DEFAULT_ARG_LABEL,
            'type' => $this->submitButton->getType()
        ];
        $this->assertEquals($expectedResult, $result);
        $this->assertArrayHasKey("name", $result);
        $this->assertArrayHasKey("title", $result);
    }
}
