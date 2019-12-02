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

    /**
     * @return array
     */
    public function validateValuesDataProvider()
    {
        $dataSet["default_scope"] = [
            [SwitchInput::ATTRIBUTE_ON => ['enabled', 1], SwitchInput::ATTRIBUTE_OFF => ['disabled', 0]],
            1,
            "OK"
        ];

        $dataSet["ON_settings_missed"] = [
            [SwitchInput::ATTRIBUTE_OFF => ['disabled', 0]],
            0,
            "Missed ON element"
        ];

        $dataSet["OFF_settings_missed"] = [
            [SwitchInput::ATTRIBUTE_ON => ['enabled', 1]],
            0,
            "Missed OFF element"
        ];

        $dataSet["not_equal_count_of_elements_properties_1"] = [
            [SwitchInput::ATTRIBUTE_ON => ['enabled', 1, 'xx'], SwitchInput::ATTRIBUTE_OFF => ['disabled', 0]],
            0,
            "Not equal count of elements properties"
        ];

        $dataSet["not_equal_count_of_elements_properties_2"] = [
            [SwitchInput::ATTRIBUTE_ON => ['enabled'], SwitchInput::ATTRIBUTE_OFF => ['disabled', 0]],
            0,
            "Not equal count of elements properties"
        ];
        $dataSet["empty_values"] = [[], 0, "Empty array"];

        $dataSet["invalid_array_structure"] = [['foo', 'bar'], 0, "Invalid structure of array"];

        $dataSet["wrong_count_of_toggle_elements"] = [
            [
                SwitchInput::ATTRIBUTE_ON => ['enabled', 1],
                SwitchInput::ATTRIBUTE_OFF => ['disabled', 0],
                "third_element" => ['foo', 2],
            ],
            0,
            "Wrong count of toggle elements. It should be equal to 2."
        ];

        $dataSet["toggle_element_is_not_array"] = [
            [
                SwitchInput::ATTRIBUTE_OFF => ['disabled', 0],
                SwitchInput::ATTRIBUTE_ON => null,
            ],

            0,
            "Toggle element should be an array"
        ];

        return $dataSet;
    }

    /**
     * @group unit
     * @small
     * @covers ::validateValues
     * @dataProvider validateValuesDataProvider
     * @param array $data
     * @param bool $expectedResult
     * @param string $message
     * @throws \ReflectionException
     */
    public function testValidateValues($data, $expectedResult, $message)
    {
        $reflection = new \ReflectionClass(get_class($this->object));
        $method = $reflection->getMethod('validateValues');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->object, [$data]);
        $this->assertEquals($expectedResult, $result, $message);
    }

    /**
     * @group unit
     * @small
     * @covers ::loadValuesFromData
     * @throws \ReflectionException
     */
    public function testLoadValuesFromData()
    {
        $onLabel = 'foo';
        $offLabel = 'bar';
        $onValue = 'X';
        $offValue = 'Y';
        $data = [
            SwitchInput::ATTRIBUTE_ON => [$onLabel, $onValue],
            SwitchInput::ATTRIBUTE_OFF => [$offLabel, $offValue]
        ];
        $reflection = new \ReflectionClass(get_class($this->object));
        $method = $reflection->getMethod('loadValuesFromData');
        $method->setAccessible(true);
        $method->invokeArgs($this->object, [$data]);
        $this->assertEquals($offLabel, $this->object->getOffLabel());
        $this->assertEquals($onLabel, $this->object->getOnLabel());
        $this->assertEquals($onValue, $this->object->getOnValue());
        $this->assertEquals($offValue, $this->object->getOffValue());
    }
}