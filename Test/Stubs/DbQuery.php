<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class DbQuery
{
    public $return = true;

    public function from($table)
    {
        return $this;
    }

    public function where($where)
    {
        if ('token = "123-456-789-123"' === $where) {
            $this->return = false;
        }
        return $this;
    }

    public function orderBy($field)
    {
        return $this;
    }
}
