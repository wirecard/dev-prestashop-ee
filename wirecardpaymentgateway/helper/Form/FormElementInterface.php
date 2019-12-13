<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Form;

/**
 * Interface FormElementInterface
 * @since 2.5.0
 * @package WirecardEE\Prestashop\Helper\Form
 */
interface FormElementInterface
{
    /**
     * @return string
     * @since 2.5.0
     */
    public function getType();

    /**
     * @return string
     * @since 2.5.0
     */
    public function getGroup();

    /**
     * @return string
     * @since 2.5.0
     */
    public function getName();

    /**
     * @return array
     * @since 2.5.0
     */
    public function build();
}
