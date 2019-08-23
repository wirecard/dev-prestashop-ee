<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

class Order
{
    public $id;

    public $current_state;

    public function __construct($order)
    {
        $this->id = 1;
    }

    public static function getIdByCartId($cartId) {
        return 102;
    }

    public function getCurrentState() {
        return "starting";
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
                'date_add' => '2019-01-01T00:00:00Z'
            ],
            1 => [
                'id_order' => 50,
                'date_add' => '2019-01-01T00:00:00Z'
            ]
        ];
    }

    public function getProducts()
    {
        return [0 => ['id_product'        => 1,
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
