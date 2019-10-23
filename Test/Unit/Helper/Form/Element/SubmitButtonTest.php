<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Test\Prestashop\Helper\Form\Element;

use WirecardEE\Prestashop\Helper\Form\Constants;
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

    /**
     * @var SubmitButton
     */
    protected $object;


    protected function setUp()
    {
        $this->object = new SubmitButton(self::DEFAULT_ARG_NAME, self::DEFAULT_ARG_LABEL);
    }

    /**
     * @group unit
     * @small
     * @covers ::getType
     */
    public function testGetType()
    {
        $this->assertEquals(Constants::FORM_ELEMENT_TYPE_SUBMIT, $this->object->getType());
    }

    /**
     * @group unit
     * @small
     * @covers ::getGroup
     */
    public function testGetGroup()
    {
        $this->assertEquals(Constants::FORM_GROUP_TYPE_SUBMIT, $this->object->getGroup());
    }

    /**
     * @group unit
     * @small
     * @covers ::build
     */
    public function testBuild()
    {
        $result = $this->object->build();
        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
        $this->assertEquals(['name' => self::DEFAULT_ARG_NAME, 'label' => self::DEFAULT_ARG_LABEL, 'type' => $this->object->getType()], $result);
        $this->assertEquals($this->object->getOptions(), $result);
        $this->assertArrayHasKey("name", $result);
        $this->assertArrayHasKey("label", $result);
    }
}