<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace Page;

class PayPalReview extends Base
{

    /**
     * @var string
     * @since 2.2.1
     */
    public $URL = 'review';

    /**
     * @var array
     * @since 2.2.1
     */
    public $elements = array(
        'Pay Now' => "//*[@id='confirmButtonTop']"
    );
}
