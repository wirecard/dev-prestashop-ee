<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Page;

class Shop extends Base
{

    /**
     * @var string
     * @since 1.3.4
     */
    // include url of current page
    public $URL = '/index.php';

    /**
     * @var array
     * @since 1.3.4
     */
    public $elements = array(
        'First Product in the Product List' => '//*[@id="content"]/section/div/article[1]/div/div[1]/h3/a',
    );
}
