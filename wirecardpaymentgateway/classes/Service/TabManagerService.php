<?php
/**
 * Shop System Extensions:
 *  - Terms of Use can be found at:
 *  https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 *  - License can be found under:
 *  https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Service;

use WirecardEE\Prestashop\Classes\Config\Tab\AdminControllerTabConfig;
use WirecardEE\Prestashop\Classes\Config\Tab\TabConfigInterface;

/**
 * Class TabManagerService
 * @package WirecardEE\Prestashop\Classes\Service
 * @since 2.5.0
 */
class TabManagerService implements ServiceInterface
{
    /**
     * @var TabConfigInterface
     * @since 2.5.0
     */
    private $tabsConfig;

    /**
     * TabManagerService constructor.
     * @param AdminControllerTabConfig[] $tabsConfig
     * @since 2.5.0
     */
    public function __construct($tabsConfig)
    {
        $this->tabsConfig = $tabsConfig;
    }

    /**
     * @since 2.5.0
     */
    public function installTabs()
    {
        /** @var TabConfigInterface $tabConfig */
        foreach ($this->tabsConfig as $tabConfig) {
            $tabId = $this->getTabId($tabConfig->getControllerName());

            $tab = new \Tab($tabId);
            $tab->active = $tabConfig->getActive();
            $tab->class_name = $tabConfig->getControllerName();
            $tab->name = $tabConfig->getName();
            $tab->icon = $tabConfig->getIcon();
            $tab->id_parent = (int) \Tab::getIdFromClassName($tabConfig->getParentControllerName());
            $tab->parent_class_name = $tabConfig->getParentControllerName();
            $tab->module = $tabConfig->getModuleName();

            $tab->save();
        }

        return true;
    }

    /**
     * @since 2.5.0
     */
    public function uninstallTabs()
    {
        /** @var TabConfigInterface $tabConfig */
        foreach ($this->tabsConfig as $tabConfig) {
            $tabId = (int) \Tab::getIdFromClassName($tabConfig->getControllerName());
            if (!$tabId) {
                continue;
            }
            $tab = new \Tab($tabId);
            $tab->delete();
        }

        return true;
    }

    /**
     * Checks if the Tab exists, if not returns null and a new Tab is created
     * else the existing is returned
     *
     * @param string $controller
     * @return int|null
     * @since 2.5.0
     */
    private function getTabId($controller)
    {
        $tabId = (int) \Tab::getIdFromClassName($controller);

        if (!$tabId) {
            $tabId = null;
        }

        return $tabId;
    }
}
