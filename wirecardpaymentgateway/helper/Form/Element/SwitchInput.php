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

namespace WirecardEE\Prestashop\Helper\Form\Element;

use Exception;
use WirecardEE\Prestashop\Classes\Constants\FormConstants;
use WirecardEE\Prestashop\Helper\TranslationHelper;

/**
 * Class SwitchInput
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Helper\Form\Element
 */
class SwitchInput extends BaseElement
{
    use TranslationHelper;

    /** @var string  */
    const ATTRIBUTE_ON = "on";

    /** @var string  */
    const ATTRIBUTE_OFF = "off";

    /** @var array */
    protected $options = [
        'required' => false,
        'class' => 't',
        'is_bool' => true,
    ];

    /** @var string */
    private $offLabel;

    /** @var mixed|string|bool|int */
    private $offValue;

    /** @var string */
    private $onLabel;

    /** @var mixed|string|bool|int */
    private $onValue;

    /** @var string */
    private $description = null;

    /**
     * @return array
     * @since 2.5.0
     */
    private function getDefaultValues()
    {
        return [
            self::ATTRIBUTE_ON => [$this->getTranslatedString('text_enabled'), 1],
            self::ATTRIBUTE_OFF => [$this->getTranslatedString('text_disabled'), 0],
        ];
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getType()
    {
        return FormConstants::FORM_ELEMENT_TYPE_SWITCH;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getGroup()
    {
        return FormConstants::FORM_GROUP_TYPE_INPUT;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getOffLabel()
    {
        return $this->offLabel;
    }

    /**
     * @param string $offLabel
     * @since 2.5.0
     */
    public function setOffLabel($offLabel)
    {
        $this->offLabel = $offLabel;
    }

    /**
     * @return bool|int|string|mixed
     * @since 2.5.0
     */
    public function getOffValue()
    {
        return $this->offValue;
    }

    /**
     * @param bool|int|string|mixed $offValue
     * @since 2.5.0
     */
    public function setOffValue($offValue)
    {
        $this->offValue = $offValue;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getOnLabel()
    {
        return $this->onLabel;
    }

    /**
     * @param string $onLabel
     * @since 2.5.0
     */
    public function setOnLabel($onLabel)
    {
        $this->onLabel = $onLabel;
    }

    /**
     * @return bool|int|string|mixed
     * @since 2.5.0
     */
    public function getOnValue()
    {
        return $this->onValue;
    }

    /**
     * @param bool|int|string|mixed $onValue
     * @since 2.5.0
     */
    public function setOnValue($onValue)
    {
        $this->onValue = $onValue;
    }

    /**
     * @return mixed
     * @since 2.5.0
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @since 2.5.0
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * SwitchInput constructor.
     * @param string $name
     * @param string $label
     * @param array $values
     * @param array $options
     * @throws Exception
     * @since 2.5.0
     */
    public function __construct($name, $label, array $values = [], array $options = [])
    {
        parent::__construct($name, $label);

        if (empty($values)) {
            $values = $this->getDefaultValues();
        }

        if (!$this->validateValues($values)) {
            throw new Exception('Wrong input!'); // todo: translation
        }

        $this->initValuesFromData($values);
        $this->optionHelper->setOptions(array_merge($this->optionHelper->getOptions(), $options));
    }

    /**
     * @param array $data
     * @return bool
     * @since 2.5.0
     */
    protected function validateValues(array $data)
    {
        $result = true;
        if (!is_array($data) || count($data) != 2) {
            $result = false;
        }

        foreach ([self::ATTRIBUTE_OFF, self::ATTRIBUTE_ON] as $key) {
            if (!isset($data[$key]) || !is_array($data[$key]) || count($data[$key]) != 2) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return SwitchInput
     * @since 2.5.0
     */
    protected function initValuesFromData(array $data)
    {
        foreach (array_keys($data) as $property) {
            if ($property == self::ATTRIBUTE_OFF) {
                $this->setOffLabel($data[$property][0]);
                $this->setOffValue($data[$property][1]);
            }
            if ($property == self::ATTRIBUTE_ON) {
                $this->setOnLabel($data[$property][0]);
                $this->setOnValue($data[$property][1]);
            }
        }

        return $this;
    }

    /**
     * @return array
     * @since 2.5.0
     */
    public function build()
    {
        $this->optionHelper->addOption('label', $this->getLabel());
        if (!$this->optionHelper->hasOption('id')) {
            $this->optionHelper->addOption('id', $this->generateUniqueId());
        }

        $unique_id = $this->optionHelper->getOption('id');

        $values = [
            ['id' => "on_{$unique_id}", 'value' => $this->getOnValue(), 'label' => $this->getOnLabel()],
            ['id' => "off_{$unique_id}", 'value' => $this->getOffValue(), 'label' => $this->getOffLabel()],
        ];

        if (!empty($this->getDescription())) {
            $this->optionHelper->addOption('desc', $this->getDescription());
        }

        $this->optionHelper->addOption('values', $values);

        return parent::build();
    }
}
