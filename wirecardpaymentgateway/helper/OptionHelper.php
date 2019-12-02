<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper;

/**
 * Standard options functionality
 * Trait OptionHelper
 * @package WirecardEE\Prestashop\Helper
 */
trait OptionHelper
{
    /**
     * @var array
     */
    protected $options = [];

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

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getOption($key)
    {
        return $this->hasOption($key) ? $this->options[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->options[$key]);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function deleteOption($key)
    {
        $deletedOption = $this->getOption($key);
        if ($this->hasOption($key)) {
            unset($this->options[$key]);
        }

        return $deletedOption;
    }
}