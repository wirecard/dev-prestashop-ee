<?php

/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Form\Element;

use WirecardEE\Prestashop\Helper\Form\FormElementInterface;
use Exception;
use WirecardEE\Prestashop\Helper\OptionHelper;

abstract class BaseElement implements FormElementInterface
{
    use OptionHelper;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function generateUniqueId()
    {
        return $this->getName() . "_" . $this->getType() . "_" . uniqid();
    }

    /**
     * BaseElement constructor.
     * @param string $name
     * @param string $label
     * @throws Exception
     */
    public function __construct($name, $label)
    {
        if (empty($name) || empty($label)) {
            throw new Exception('Wrong input!'); // translation
        }

        $this->setName($name);
        $this->setLabel($label);
        $this->addOption('type', $this->getType());
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->addOption('name', $this->getName());
        return $this->getOptions();
    }
}
