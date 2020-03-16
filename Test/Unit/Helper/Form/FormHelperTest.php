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
use WirecardEE\Prestashop\Helper\Form\Element\SwitchInput;
use WirecardEE\Prestashop\Helper\Form\FormElementInterface;
use WirecardEE\Prestashop\Helper\Form\FormHelper;

/**
 * Class FormHelperTest
 * @package WirecardEE\Test\Prestashop\Helper\Form
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\Form\FormHelper
 */
class FormHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormHelper
     */
    protected $formHelper;


    protected function setUp()
    {
        $this->formHelper = new FormHelper();
    }

    /**
     * @group unit
     * @small
     * @covers ::getElements
     */
    public function testOnInit()
    {
        $this->assertCount(0, $this->formHelper->getElements());
    }

    /**
     * @group unit
     * @small
     * @covers ::addElement
     */
    public function testAddElement()
    {
        $this->assertCount(0, $this->formHelper->getElements());
        $submitBtn = new SubmitButton("test", "test1");
        $this->formHelper->addElement($submitBtn);
        $this->assertNotEmpty($this->formHelper->getElements());
        $this->assertCount(1, $this->formHelper->getElements());

        $this->assertInstanceOf(FormElementInterface::class, $this->formHelper->getElements()[0]);
    }

    /**
     * @group unit
     * @small
     * @covers ::addSubmitButton
     * @throws \Exception
     */
    public function testAddSubmitButton()
    {
        $this->assertCount(0, $this->formHelper->getElements());
        $fieldName = "testFieldName";
        $fieldLabel = "foo";
        $submitBtn = new SubmitButton($fieldName, $fieldLabel);
        $this->formHelper->addSubmitButton($fieldName, $fieldLabel);
        $this->assertCount(1, $this->formHelper->getElements());
        $element = $this->formHelper->getElements()[0];
        $this->assertEquals(FormConstants::FORM_GROUP_TYPE_SUBMIT, $element->getGroup());
        $this->assertEquals(FormConstants::FORM_ELEMENT_TYPE_SUBMIT, $element->getType());
        $this->assertInstanceOf(FormElementInterface::class, $element);
        $this->assertEquals($submitBtn, $element);
    }

    /**
     * @group unit
     * @small
     * @covers ::addSwitchInput
     * @throws \Exception
     */
    public function testAddSwitchInput()
    {
        $fieldName = "testFieldName";
        $fieldLabel = "foo";
        $this->assertCount(0, $this->formHelper->getElements());
        $switchItem = new SwitchInput($fieldName, $fieldLabel);
        $this->formHelper->addSwitchInput($fieldName, $fieldLabel);
        $this->assertCount(1, $this->formHelper->getElements());
        $element = $this->formHelper->getElements()[0];
        $this->assertEquals(FormConstants::FORM_GROUP_TYPE_INPUT, $element->getGroup());
        $this->assertEquals(FormConstants::FORM_ELEMENT_TYPE_SWITCH, $element->getType());
        $this->assertInstanceOf(FormElementInterface::class, $element);
        $this->assertEquals($switchItem, $element);
    }

    public function buildFormDataProvider()
    {
        // $elements | has_input | has_submit | input_count
        $dataSet = [];

        $dataSet[] = [
            [
                new SubmitButton("submitBtnName", "submitBtnLabel"),
                new SwitchInput("switchInputName", "switchInputLabel")
            ],
            true,
            true,
            1
        ];

        $dataSet[] = [
            [
                new SubmitButton("submitBtnName", "submitBtnLabel"),
                new SwitchInput("switchInputName", "switchInputLabel"),
                new SwitchInput("switchInputName", "switchInputLabel"),
                new SwitchInput("switchInputName", "switchInputLabel"),
            ],
            true,
            true,
            3
        ];

        $dataSet[] = [
            [
                new SubmitButton("submitBtnName", "submitBtnLabel")
            ],
            false,
            true,
            0
        ];

        $dataSet[] = [[], false, false, 0];

        $dataSet[] = [
            [
                new SwitchInput("switchInputName", "switchInputLabel"),
            ],
            true,
            false,
            1
        ];

        return $dataSet;
    }

    /**
     * @group unit
     * @small
     * @covers ::buildForm
     * @dataProvider buildFormDataProvider
     * @param array $elements
     * @param bool $has_input
     * @param bool $has_submit
     * @param int $input_count
     */
    public function testBuildForm($elements, $has_input, $has_submit, $input_count)
    {
        foreach ($elements as $element) {
            $this->formHelper->addElement($element);
        }

        $result = $this->formHelper->buildForm();

        $this->assertTrue(is_array($result));
        $this->assertEquals($has_input, isset($result['input']));
        $this->assertEquals($has_submit, isset($result['submit']));
        $this->assertEquals($input_count, isset($result['input']) ? count($result['input']) : 0);
    }

    /**
     * @group unit
     * @small
     * @covers ::getFormValues
     * @throws \Exception
     */
    public function testGetFormValues()
    {
        $result = $this->formHelper->getFormValues();
        $this->assertEmpty($result);
        $this->formHelper->addSubmitButton("foo", "bar");
        $result = $this->formHelper->getFormValues();
        $this->assertEmpty($result);
        $this->formHelper->addSwitchInput("fooSwitch", "barSwitch");

        $result = $this->formHelper->getFormValues();
        $this->assertNotEmpty($result);
        $this->assertEquals(array (
            'fooSwitch' => 'fooSwitch'
        ), $result);
        $this->formHelper->addSwitchInput("fooSwitch1", "barSwitch1");
        $result = $this->formHelper->getFormValues();
        $this->assertEquals(array (
            'fooSwitch' => 'fooSwitch',
            'fooSwitch1' => 'fooSwitch1',
        ), $result);
    }
}
