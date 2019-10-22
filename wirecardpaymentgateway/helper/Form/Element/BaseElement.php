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

abstract class BaseElement implements FormElementInterface
{
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
     * @var array
     */
    protected $options = [];

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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param $option
     * @param $value
     */
    public function addOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function getOption($key)
    {
        $options = $this->getOptions();
        return $this->hasOption($key) ? $options[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasOption($key)
    {
        $options = $this->getOptions();
        return isset($options[$key]);
    }

    /**
     * @return string
     */
    public function generateId()
    {
        return $this->getName() . "_" . $this->getType();
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
        $this->addOption('label', $this->getLabel());
        return $this->getOptions();
    }
}