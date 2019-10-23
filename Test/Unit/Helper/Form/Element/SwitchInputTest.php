<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Test\Prestashop\Helper\Form\Element;

require_once __DIR__ . '/../../../../../wirecardpaymentgateway/wirecardpaymentgateway.php';

use WirecardEE\Prestashop\Helper\Form\Constants;
use WirecardEE\Prestashop\Helper\Form\Element\SwitchInput;

/**
 * Class SwitchInputTest
 * @package WirecardEE\Test\Prestashop\Helper\Form\Element
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\Form\Element\SwitchInput
 */
class SwitchInputTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DEFAULT_TYPE = "default";
    const DEFAULT_ARG_NAME = "foo";
    const DEFAULT_ARG_LABEL = "bar";

    /**
     * @var SwitchInput
     */
    protected $object;


    protected function setUp()
    {
        $this->object = new SwitchInput(self::DEFAULT_ARG_NAME, self::DEFAULT_ARG_LABEL);
    }

    /**
     * @group unit
     * @small
     * @covers ::getType
     */
    public function testGetType()
    {
        $this->assertEquals(Constants::FORM_ELEMENT_TYPE_SWITCH, $this->object->getType());
    }

    /**
     * @group unit
     * @small
     * @covers ::getGroup
     */
    public function testGetGroup()
    {
        $this->assertEquals(Constants::FORM_GROUP_TYPE_INPUT, $this->object->getGroup());
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
        $this->assertEquals($this->object->getOptions(), $result);
        $this->assertArrayHasKey("name", $result);
        $this->assertArrayHasKey("label", $result);
        $this->assertArrayHasKey("values", $result);
    }
}