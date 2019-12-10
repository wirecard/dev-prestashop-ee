<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Config\Tab;

class AdminControllerTabConfig implements TabConfigInterface
{
    /**
     * @var string
     */
    private $controller;

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
    private $parentController;

    /**
     * AdminControllerTabConfig constructor.
     * @param string $name
     * @param string $controller
     * @param string $moduleName
     * @param string $icon
     * @param int $active
     * @param null $parentController
     */
    public function __construct($name, $controller, $moduleName, $icon = '', $active = 1, $parentController = null)
    {
        $this->controller = $controller;
        $this->moduleName = $moduleName;
        $this->active = $active;
        $this->name = $this->createNameWithTranslations($name);
        $this->icon = $icon;
        $this->parentController = $parentController;
    }

    /**
     * @return string
     * @since 2.5.0
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string|null
     * @since 2.5.0
     */
    public function getParentController()
    {
        return $this->parentController;
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
