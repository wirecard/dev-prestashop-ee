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
use WirecardEE\Prestashop\Helper\TranslationHelper;
use Exception;

class SwitchInput extends BaseElement implements FormElementInterface
{
    use TranslationHelper;

    const ATTRIBUTE_ON = "on";

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

    private function getDefaultValues()
    {
        return [
            self::ATTRIBUTE_ON => [$this->getTranslatedString('text_enabled'), 1],
            self::ATTRIBUTE_OFF => [$this->getTranslatedString('text_disabled'), 0],
        ];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Constants::FORM_ELEMENT_TYPE_SWITCH;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return Constants::FORM_GROUP_TYPE_INPUT;
    }

    /**
     * @return string
     */
    public function getOffLabel()
    {
        return $this->offLabel;
    }

    /**
     * @param string $offLabel
     */
    public function setOffLabel($offLabel)
    {
        $this->offLabel = $offLabel;
    }

    /**
     * @return bool|int|string|mixed
     */
    public function getOffValue()
    {
        return $this->offValue;
    }

    /**
     * @param bool|int|string|mixed $offValue
     */
    public function setOffValue($offValue)
    {
        $this->offValue = $offValue;
    }

    /**
     * @return string
     */
    public function getOnLabel()
    {
        return $this->onLabel;
    }

    /**
     * @param string $onLabel
     */
    public function setOnLabel($onLabel)
    {
        $this->onLabel = $onLabel;
    }

    /**
     * @return bool|int|string|mixed
     */
    public function getOnValue()
    {
        return $this->onValue;
    }

    /**
     * @param bool|int|string|mixed $onValue
     */
    public function setOnValue($onValue)
    {
        $this->onValue = $onValue;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function validateValues(array $data)
    {
        $result = true;
        $result &= is_array($data);
        $result &= (count($data) == 2);

        foreach ([self::ATTRIBUTE_OFF, self::ATTRIBUTE_ON] as $key) {
            $result &= (isset($data[$key]) & is_array($data[$key]) || count($data[$key]) == 2);
        }

        return true;
    }

    /**
     * @param array $data
     * @return SwitchInput
     */
    protected function loadValuesFromData(array $data)
    {
        list($offLabel, $offValue) = $data[self::ATTRIBUTE_OFF];
        $this->setOffLabel($offLabel);
        $this->setOffValue($offValue);
        list($onLabel, $onValue) = $data[self::ATTRIBUTE_ON];
        $this->setOnLabel($onLabel);
        $this->setOnValue($onValue);

        return $this;
    }

    /**
     * SwitchInput constructor.
     * @param string $name
     * @param string $label
     * @param array $values
     * @param array $options
     * @throws Exception
     */
    public function __construct($name, $label, array $values = [], $options = [])
    {
        parent::__construct($name, $label);

        if (empty($values)) {
            $values = $this->getDefaultValues();
        }

        if (!$this->validateValues($values)) {
            throw new Exception('Wrong input!'); // todo: translation
        }

        $this->loadValuesFromData($values);
        $this->setOptions(array_merge($this->getOptions(), $options));
    }

    /**
     * @return array
     */
    public function build()
    {
        if (!$this->hasOption('id')) {
            $this->addOption('id', $this->generateId());
        }

        $id = $this->getOption('id');
        $values = [
            ['id' => "on_{$id}", 'value' => $this->getOnValue(), 'label' => $this->getOnLabel()],
            ['id' => "off_{$id}", 'value' => $this->getOffValue(), 'label' => $this->getOffLabel()],
        ];

        $this->addOption('values', $values);

        return parent::build();
    }
}