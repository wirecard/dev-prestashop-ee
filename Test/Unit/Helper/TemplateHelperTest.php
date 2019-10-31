<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

use WirecardEE\Prestashop\Helper\TemplateHelper;

class TemplateHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsCorrectPath()
    {
        $path = TemplateHelper::getTemplatePath('creditcard');

        $this->assertEquals(
            'modules/wirecardpaymentgateway/views/templates/front/creditcard.tpl',
            $path
        );
    }
}
