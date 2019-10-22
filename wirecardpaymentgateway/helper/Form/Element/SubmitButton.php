<?php

/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Form\Element;

use WirecardEE\Prestashop\Helper\Form\Constants;
use WirecardEE\Prestashop\Helper\Form\FormElementInterface;

class SubmitButton extends BaseElement implements FormElementInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return Constants::FORM_ELEMENT_TYPE_SUBMIT;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return Constants::FORM_GROUP_TYPE_SUBMIT;
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getLabel(),
        ];
    }
}