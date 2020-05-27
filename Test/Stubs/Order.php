<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

class Order
{
    public $id;

    public $current_state;

    public function __construct($order_id = null)
    {
        $this->id = 1;
        if ($order_id) {
            $this->id = $order_id;
        }
    }

    public function addOrderPayment($amount_paid, $payment_method = null, $payment_transaction_id = null) {
        return true;
    }

    public static function getIdByCartId($cartId) {
        return 102;
    }

    public function getCurrentState() {
        return "starting";
    }

    public function setCurrentState($state) {
        $this->current_state = $state;
    }

    /**
     * Get customer orders.
     *
     * @param int $id_customer Customer id
     * @param bool $show_hidden_status Display or not hidden order statuses
     *
     * @return array Customer orders
     */
    public static function getCustomerOrders($id_customer, $show_hidden_status = false, Context $context = null)
    {
        return [
            0 => [
                'id_order' => 1,
                'valid' => 1,
                'date_add' => '2018-01-01T08:00:00Z'
            ],
            1 => [
                'id_order' => 2,
                'valid' => 1,
                'date_add' => '2019-01-01T11:00:00Z'
            ],
            2 => [
                'id_order' => 3,
                'valid' => 1,
                'date_add' => '2019-03-02T12:00:00Z'
            ],
            3 => [
                'id_order' => 4,
                'valid' => 1,
                'date_add' => '2019-04-01T00:00:00Z'
            ],
            4 => [
                'id_order' => 5,
                'valid' => 1,
                'date_add' => '2019-04-02T00:00:00Z'
            ],
            5 => [
                'id_order' => 6,
                'valid' => 0,
                'date_add' => '2019-04-02T00:00:00Z'
            ]
        ];
    }

    public function getProducts()
    {
        return [
            0 => [
                'id_product'        => 1,
                'cart_quantity'     => 1,
                'total_wt'          => 2,
                'name'              => 'Product 1',
                'total'             => 100,
                'description_short' => 'short desc',
                'reference'         => 'reference'
            ]
        ];
    }
}
