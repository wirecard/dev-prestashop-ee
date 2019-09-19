<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class OrderState
{
    const FLAG_NO_HIDDEN = 1;  /* 00001 */
    const FLAG_LOGABLE = 2;  /* 00010 */
    const FLAG_DELIVERY = 4;  /* 00100 */
    const FLAG_SHIPPED = 8;  /* 01000 */
    const FLAG_PAID = 16; /* 10000 */

    public $id;

    public static function getOrderStates($id)
    {
        return array(array('id_order_state' => 'id', 'name' => 'name'));
    }

    public static function add()
    {
        return;
    }
}
