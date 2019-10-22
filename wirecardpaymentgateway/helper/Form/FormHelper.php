<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Form;

use WirecardEE\Prestashop\Helper\Form\Element\SubmitButton;
use WirecardEE\Prestashop\Helper\Form\Element\SwitchInput;

class FormHelper extends \HelperFormCore
{
    /** @var array|FormElementInterface[] */
    private $elements = [];

    /**
     * @return array|FormElementInterface[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array|FormElementInterface $element
     * @return FormHelper
     */
    public function addElement(FormElementInterface $element)
    {
        $this->elements[] = $element;
        return $this;
    }


    /**
     * @param $fieldName
     * @param $label
     * @param array $values
     * @param array $options
     * @throws \Exception
     */
    public function addSwitchInput($fieldName, $label, $values = [], $options = [])
    {
        $this->elements[] = new SwitchInput($fieldName, $label, $values, $options);
    }

    /**
     * @param string $name
     * @param string $label
     */
    public function addSubmitButton($name, $label)
    {
        $this->elements[] = new SubmitButton($name, $label);
    }

    /**
     * @return array
     */
    public function buildForm()
    {
        $elements = [];
        foreach ($this->getElements() as $formElement) {
            if ($formElement->getGroup() == Constants::FORM_GROUP_TYPE_SUBMIT) {
                $elements[$formElement->getGroup()] = $formElement->build();
            }
            $elements[$formElement->getGroup()][] = $formElement->build();
        }

        return $elements;
    }

    /**
     * @return array
     */
    public function getFormValues()
    {
        $elementValues = [];
        foreach ($this->getElements() as $formElement) {
            if ($formElement->getGroup() != Constants::FORM_GROUP_TYPE_INPUT) {
                continue;
            }
            $elementValues[$formElement->getName()] = \Configuration::get($formElement->getName());
        }
        return $elementValues;
    }
}