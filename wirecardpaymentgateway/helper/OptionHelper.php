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
 * @since 2.5.0
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
     * @since 2.5.0
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @since 2.5.0
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param string $option
     * @param mixed $value
     * @since 2.5.0
     */
    public function addOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     * @since 2.5.0
     */
    public function getOption($key)
    {
        return $this->hasOption($key) ? $this->options[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     * @since 2.5.0
     */
    public function hasOption($key)
    {
        return isset($this->options[$key]);
    }

    /**
     * @param string $key
     * @return mixed|null
     * @since 2.5.0
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
