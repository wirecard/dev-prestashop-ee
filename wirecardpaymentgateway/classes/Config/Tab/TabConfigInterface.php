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
 * Interface TabConfigInterface
 * @package WirecardEE\Prestashop\Classes\Config\Tab
 * @since 2.5.0
 */
interface TabConfigInterface
{
    /**
     * Controller name that handel's the tab
     * @return string
     */
    public function getController();

    /**
     * ParentController name
     * @return string
     */
    public function getParentController();

    /**
     * Module name
     * @return string
     */
    public function getModuleName();

    /**
     * 1 or 0 default its active and 1
     * @return int
     */
    public function getActive();

    /**
     * Its a array of the presented name structure below
     * [
     *    langId => 'Name',
     *    1 => 'Transaction'
     *    2 => 'Transaktion'
     * ]
     * @return array
     */
    public function getName();

    /**
     * Icon key
     * @return string
     */
    public function getIcon();
}
