<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Helper\Form;

interface FormElementInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return array
     */
    public function build();
}