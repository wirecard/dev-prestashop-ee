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
use Exception;
use HelperForm;
use Configuration;

class FormHelper extends HelperForm
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
     * @param string $fieldName
     * @param string $label
     * @param array $values
     * @param array $options
     * @return FormHelper
     * @throws Exception
     */
    public function addSwitchInput($fieldName, $label, $values = [], $options = [])
    {
        $this->addElement(new SwitchInput($fieldName, $label, $values, $options));
        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $label
     * @throws Exception
     */
    public function addSubmitButton($fieldName, $label)
    {
        $this->addElement(new SubmitButton($fieldName, $label));
    }

    /**
     * @return array
     */
    public function buildForm()
    {
        $elements = [];
        foreach ($this->getElements() as $formElement) {
            if ($formElement->getGroup() == Constants::FORM_GROUP_TYPE_INPUT) {
                $elements[$formElement->getGroup()][] = $formElement->build();
            }
            $elements[$formElement->getGroup()] = $formElement->build();
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
            $elementValues[$formElement->getName()] = Configuration::get($formElement->getName());
        }
        return $elementValues;
    }
}