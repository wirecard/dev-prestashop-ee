<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Classes\Finder;

use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

/**
 * Class DbFinder
 * @package WirecardEE\Prestashop\Classes\Finder
 */
class DbFinder
{
    /** @var DbQuery */
    private $queryBuilder;
    
    /**
     * @var array|Db
     */
    private $database;

    public function __construct()
    {
        $this->database = Db::getInstance();
        $this->queryBuilder = new DbQuery();
    }

    /**
     * @return array|\Db|Db
     */
    public function getDb()
    {
        return $this->database;
    }

    /**
     * @return DbQuery
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
