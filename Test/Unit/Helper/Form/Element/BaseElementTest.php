<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Test\Helper\Form\Element;

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
    protected $baseElement;


    protected function setUp()
    {
        $this->baseElement = $this->getMockForAbstractClass(
            BaseElement::class,
            [self::DEFAULT_ARG_NAME, self::DEFAULT_ARG_LABEL]
        );
        $this->baseElement->expects($this->any())->method('getType')->willReturn(self::TEST_DEFAULT_TYPE);
    }

    /**
     * @group unit
     * @small
     * @expectedException \Exception
     */
    public function testConstructorException()
    {
        $this->getMockForAbstractClass(BaseElement::class, ["", ""]);
    }

    /**
     * @group unit
     * @small
     */
    public function testConstructor()
    {
        $this->assertEquals(self::DEFAULT_ARG_NAME, $this->baseElement->getName());
        $this->assertEquals(self::DEFAULT_ARG_LABEL, $this->baseElement->getLabel());
    }

    /**
     * @group unit
     * @small
     * @covers ::generateUniqueId
     */
    public function testGenerateId()
    {
        $prefix = self::DEFAULT_ARG_NAME . "_" . self::TEST_DEFAULT_TYPE;
        $this->assertContains($prefix, $this->baseElement->generateUniqueId());
    }

    /**
     * @group unit
     * @small
     * @covers ::build
     */
    public function testBuild()
    {
        $result = $this->baseElement->build();
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertEquals(['name' => self::DEFAULT_ARG_NAME, 'type' => null], $result);
        $this->assertArrayHasKey("name", $result);
    }
}
