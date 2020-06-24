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

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

/**
 * Create new menu entry Wirecard Transactions in Sell part of admin menu
 */


if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @return mixed
 */
function upgrade_module_2_1_0()
{
    $symfonyContainer = SymfonyContainer::getInstance();
    $tabRepository = $symfonyContainer->get('prestashop.core.admin.tab.repository');
    $tab = new Tab($tabRepository->findOneIdByClassName('WirecardTransactions'));
    $tab->icon = 'payment';
    // Show on Sell part of menu
    $tab->id_parent = 2;
    $tab->parent_class_name = 'SELL';
    return $tab->update();
}
