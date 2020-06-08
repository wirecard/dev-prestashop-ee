<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Db
{
    protected static $instance;

    private $return;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Db();
        }

        return self::$instance;
    }

    public static function execute($query, $use_cache = true)
    {
        return true;
    }

    public static function executeS($query)
    {
        return $query;
    }

    public static function getRow($query)
    {
    	if (is_object($query)) {
		    return $query->return;
	    }
    }

    public static function delete()
    {
        return true;
    }


    /**
     * Executes an UPDATE query.
     *
     * @param string $table Table name without prefix
     * @param array $data Data to insert as associative array. If $data is a list of arrays, multiple insert will be done
     * @param string $where WHERE condition
     * @param int $limit
     * @param bool $null_values If we want to use NULL values instead of empty quotes
     * @param bool $use_cache
     * @param bool $add_prefix Add or not _DB_PREFIX_ before table name
     *
     * @return bool
     */
    public function update($table, $data, $where = '', $limit = 0, $null_values = false, $use_cache = true, $add_prefix = true)
    {
        return true;
    }
}
