<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Prestashop\Test\Helper;

use WirecardEE\Prestashop\Helper\OptionHelper;

/**
 * Class OptionHelperTest
 * @package WirecardEE\Test\Prestashop\Helper
 * @coversDefaultClass \WirecardEE\Prestashop\Helper\OptionHelper
 */
class OptionHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionHelper
     */
    protected $optionHelper;

    protected function setUp()
    {
        $this->optionHelper = $this->getMockForTrait(OptionHelper::class);
    }

    /**
     * @group unit
     * @small
     * @covers ::addOption
     */
    public function testAddOption()
    {
        $this->optionHelper->addOption('foo', 'bar');
        $options = $this->optionHelper->getOptions();
        $this->assertArrayHasKey('foo', $options);
        $this->assertEquals('bar', $options['foo']);
    }

    /**
     * @group unit
     * @small
     * @covers ::getOptions
     */
    public function testGetOptions()
    {
        $this->assertInternalType('array', $this->optionHelper->getOptions());
        $this->optionHelper->addOption('foo', 'bar');
        $this->optionHelper->addOption('foo', 'bar');
        $this->optionHelper->addOption('foo', 'bar');
        $this->assertCount(1, $this->optionHelper->getOptions());
        $this->optionHelper->addOption('bar', 'foo');
        $this->assertCount(2, $this->optionHelper->getOptions());
    }

    /**
     * @group unit
     * @small
     * @covers ::getOption
     */
    public function testGetOption()
    {
        $this->optionHelper->addOption('foo', 'bar');
        $this->assertEquals('bar', $this->optionHelper->getOption('foo'));
        $this->assertEquals(null, $this->optionHelper->getOption('baz'));
    }

    /**
     * @group unit
     * @small
     * @covers ::hasOption
     */
    public function testHasOption()
    {
        $this->optionHelper->addOption('foo', 'bar');
        $this->assertEquals(true, $this->optionHelper->hasOption('foo'));
        $this->assertEquals(false, $this->optionHelper->hasOption('baz'));
    }

    /**
     * @group unit
     * @small
     * @covers ::deleteOption
     */
    public function testDeleteOption()
    {
        $this->optionHelper->addOption('foo', 'bar');
        $this->assertCount(1, $this->optionHelper->getOptions());
        $deletedOption = $this->optionHelper->deleteOption('foo');
        $this->assertEquals('bar', $deletedOption);
        $this->assertCount(0, $this->optionHelper->getOptions());
        $this->assertEquals(null, $this->optionHelper->deleteOption('foo'));
        $this->assertEquals(null, $this->optionHelper->deleteOption('baz'));
    }
}
