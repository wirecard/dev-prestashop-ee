<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config\Tab;

/**
 * Class AdminControllerTabConfig
 * @package WirecardEE\Prestashop\Classes\Config\Tab
 * @since 2.5.0
 */
class AdminControllerTabConfig implements TabConfigInterface
{
    /**
     * @var string
     */
    private $controllerName;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var int
     */
    private $active;

    /**
     * @var array
     */
    private $name;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var null|string
     */
    private $parentControllerName;

    /**
     * AdminControllerTabConfig constructor.
     * @param string $moduleName
     * @param string $keyName
     * @param string $controllerName
     * @param string $icon
     * @param int $active
     * @param null|string $parentControllerName
     * @since 2.5.0
     */
    public function __construct(
        $moduleName,
        $keyName,
        $controllerName,
        $icon = '',
        $active = 1,
        $parentControllerName = null
    ) {
        $this->moduleName = $moduleName;
        $this->name = $this->createNameWithTranslations($keyName);
        $this->controllerName = $controllerName;
        $this->icon = $icon;
        $this->active = $active;
        $this->parentControllerName = $parentControllerName;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @return string|null
     * @since 2.5.0
     */
    public function getParentControllerName()
    {
        return $this->parentControllerName;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @return int
     * @since 2.5.0
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return array
     * @since 2.5.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $nameKey
     * @return array
     * @since 2.5.0
     */
    private function createNameWithTranslations($nameKey)
    {
        $translatedNames = [];
        foreach (\Language::getLanguages(true) as $language) {
            $translated_string = \WirecardPaymentGateway::getTranslationForLanguage(
                $language['iso_code'],
                $nameKey,
                $this->moduleName
            );
            $translatedNames[$language['id_lang']] = $translated_string;
        }

        return $translatedNames;
    }
}
