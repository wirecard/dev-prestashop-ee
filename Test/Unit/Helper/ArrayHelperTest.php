<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Test\Helper;

use WirecardEE\Prestashop\Helper\ArrayHelper;

/**
 * Class ArrayHelperTest
 * @package WirecardEE\Test\Prestashop\Helper
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\ArrayHelper
 */
class ArrayHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function filterWithPrefixDataProvider()
    {
        $dataSet = [];
        $dataSet['array_without_results'] = [
            ['foo' => 'bar', 'baz' => 'foo'],
            'prefix_',
            []
        ];
        $dataSet['array_with_prefix'] = [
            ['prefix_foo' => 'bar', 'baz' => 'foo'],
            'prefix_',
            ['prefix_foo' => 'bar']
        ];
        $dataSet['array_with_prefix_1'] = [
            ['prefix_prefix_foo' => 'bar', 'baz' => 'foo'],
            'prefix_',
            ['prefix_prefix_foo' => 'bar']
        ];
        $dataSet['array_with_prefix_2'] = [
            ['prefix_foo' => 'bar', 'prefix_baz' => 'foo'],
            'prefix_',
            ['prefix_foo' => 'bar', 'prefix_baz' => 'foo']
        ];
        return $dataSet;
    }

    /**
     * @group unit
     * @small
     * @covers ::filterWithPrefix
     * @dataProvider filterWithPrefixDataProvider
     * @param array $data
     * @param array $expectedResult
     * @param string $prefix
     */
    public function testFilterWithPrefix($data, $prefix, $expectedResult)
    {
        $this->assertEquals($expectedResult, ArrayHelper::filterWithPrefix($data, $prefix));
    }
}
