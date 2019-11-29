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
    protected $object;


    protected function setUp()
    {
        $this->object = new FormHelper();
    }

    /**
     * @group unit
     * @small
     * @covers ::getElements
     */
    public function testOnInit()
    {
        $this->assertCount(0, $this->object->getElements());
    }

    /**
     * @group unit
     * @small
     * @covers ::addElement
     */
    public function testAddElement()
    {
        $this->assertCount(0, $this->object->getElements());
        $submitBtn = new SubmitButton("test", "test1");
        $this->object->addElement($submitBtn);
        $this->assertNotEmpty($this->object->getElements());
        $this->assertCount(1, $this->object->getElements());

        $this->assertInstanceOf(FormElementInterface::class, $this->object->getElements()[0]);
    }

    /**
     * @group unit
     * @small
     * @covers ::addSubmitButton
     * @throws \Exception
     */
    public function testAddSubmitButton()
    {
        $this->assertCount(0, $this->object->getElements());
        $fieldName = "testFieldName";
        $fieldLabel = "foo";
        $submitBtn = new SubmitButton($fieldName, $fieldLabel);
        $this->object->addSubmitButton($fieldName, $fieldLabel);
        $this->assertCount(1, $this->object->getElements());
        $element = $this->object->getElements()[0];
        $this->assertEquals(Constants::FORM_GROUP_TYPE_SUBMIT, $element->getType());
        $this->assertInstanceOf(FormElementInterface::class, $element);
        $this->assertEquals($submitBtn, $element);
    }

    public function testAddSwitchInput()
    {
        $this->assertCount(0, $this->object->getElements());
    }
}