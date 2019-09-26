<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Page;

class Product extends Base
{

    /**
     * @var string
     * @since 1.3.4
     */
    // include url of current page
    public $URL = 'index.php?id_product=1&id_product_attribute=1&rewrite=hummingbird-printed-t-shirt&controller=product#/1-size-s/8-color-white';

    /**
     * @var array
     * @since 1.3.4
     */
    public $elements = array(
        'Quantity' => '//*[@id="quantity_wanted"]',
        'Add to cart' => "//*[@class='btn btn-primary add-to-cart']",
        'Cart'        => "//*[@class='material-icons shopping-cart']",
    );
}
