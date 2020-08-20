<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 * @author Wirecard AG
 * @copyright Copyright (c) 2020 Wirecard AG, Einsteinring 35, 85609 Aschheim, Germany
 * @license MIT License
 */

namespace WirecardEE\Prestashop\Helper\Form;

use WirecardEE\Prestashop\Classes\Constants\FormConstants;
use WirecardEE\Prestashop\Helper\Form\Element\SubmitButton;
use WirecardEE\Prestashop\Helper\Form\Element\SwitchInput;

/**
 * Class FormHelper
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Helper\Form
 */
class FormHelper extends \HelperForm
{
    /** @var array|FormElementInterface[] */
    private $elements = [];

    /**
     * @return array|FormElementInterface[]
     * @since 2.5.0
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array|FormElementInterface $element
     * @return FormHelper
     * @since 2.5.0
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
     * @throws \Exception
     * @since 2.5.0
     */
    public function addSwitchInput($fieldName, $label, $values = [], $options = [])
    {
        $this->addElement(new SwitchInput($fieldName, $label, $values, $options));
        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $label
     * @throws \Exception
     * @since 2.5.0
     */
    public function addSubmitButton($fieldName, $label)
    {
        $this->addElement(new SubmitButton($fieldName, $label));
    }

    /**
     * @return array
     * @since 2.5.0
     */
    public function buildForm()
    {
        $elements = [];
        foreach ($this->getElements() as $formElement) {
            if (in_array($formElement->getGroup(), FormConstants::getGroupTypesWithChildren())) {
                $elements[$formElement->getGroup()][] = $formElement->build();
                continue;
            }
            $elements[$formElement->getGroup()] = $formElement->build();
        }

        return $elements;
    }

    /**
     * @return array
     * @since 2.5.0
     */
    public function getFormValues()
    {
        $elementValues = [];
        foreach ($this->getElements() as $formElement) {
            if (!in_array($formElement->getType(), FormConstants::getElementTypesWithValues())) {
                continue;
            }
            $elementValues[$formElement->getName()] = $this->getValueByElementName($formElement->getName());
        }
        return $elementValues;
    }

    /**
     * @param mixed $name
     * @return int|string|mixed
     * @since 2.5.0
     */
    protected function getValueByElementName($name)
    {
        return \Configuration::get($name);
    }
}
