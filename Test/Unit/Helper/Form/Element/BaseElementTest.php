<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Test\Prestashop\Helper\Form\Element;

use WirecardEE\Prestashop\Helper\Form\Element\BaseElement;

/**
 * Class BaseElementTest
 * @package WirecardEE\Test\Prestashop\Helper\Form\Element
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\Form\Element\BaseElement
 */
class BaseElementTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DEFAULT_TYPE = "default";
    const DEFAULT_ARG_NAME = "foo";
    const DEFAULT_ARG_LABEL = "bar";

    /**
     * @var BaseElement
     */
    protected $object;


    protected function setUp()
    {
        $this->object = $this->getMockForAbstractClass(BaseElement::class,
            [self::DEFAULT_ARG_NAME, self::DEFAULT_ARG_LABEL]);
    }

    /**
     * @group unit
     * @small
     * @expectedException \Exception
     */
    public function testConstructor_Exception()
    {
        $this->object = $this->getMockForAbstractClass(BaseElement::class, ["", ""]);
    }

    /**
     * @group unit
     * @small
     */
    public function testConstructor()
    {
        $this->assertEquals(self::DEFAULT_ARG_NAME, $this->object->getName());
        $this->assertEquals(self::DEFAULT_ARG_LABEL, $this->object->getLabel());
    }

    /**
     * @group unit
     * @small
     * @covers ::generateId
     */
    public function testGenerateId()
    {
        $this->object = $this->getMockForAbstractClass(BaseElement::class,
            [self::DEFAULT_ARG_NAME, self::DEFAULT_ARG_LABEL]);

        $this->object->expects($this->once())->method('getType')->will($this->returnValue(self::TEST_DEFAULT_TYPE));
        $this->assertEquals(self::DEFAULT_ARG_NAME . "_" . self::TEST_DEFAULT_TYPE, $this->object->generateId());
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
        $this->assertEquals(['name' => self::DEFAULT_ARG_NAME, 'label' => self::DEFAULT_ARG_LABEL, 'type' => null], $result);
        $this->assertEquals($this->object->getOptions(), $result);
        $this->assertArrayHasKey("name", $result);
        $this->assertArrayHasKey("label", $result);
    }
}