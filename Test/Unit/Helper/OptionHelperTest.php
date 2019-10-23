<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/prestashop-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/prestashop-ee/blob/master/LICENSE
 */

namespace WirecardEE\Test\Prestashop\Helper;

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
    protected $object;

    protected function setUp()
    {
        $this->object = $this->getMockForTrait(OptionHelper::class);
    }

    /**
     * @group unit
     * @small
     * @covers ::addOption
     */
    public function testAddOption()
    {
        $this->object->addOption('foo', 'bar');
        $options = $this->object->getOptions();
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
        $this->assertTrue(is_array($this->object->getOptions()));
        $this->object->addOption('foo', 'bar');
        $this->object->addOption('foo', 'bar');
        $this->object->addOption('foo', 'bar');
        $this->assertCount(1, $this->object->getOptions());
        $this->object->addOption('bar', 'foo');
        $this->assertCount(2, $this->object->getOptions());
    }

    /**
     * @group unit
     * @small
     * @covers ::getOption
     */
    public function testGetOption()
    {
        $this->object->addOption('foo', 'bar');
        $this->assertEquals('bar', $this->object->getOption('foo'));
        $this->assertEquals(null, $this->object->getOption('baz'));
    }

    /**
     * @group unit
     * @small
     * @covers ::hasOption
     */
    public function testHasOption()
    {
        $this->object->addOption('foo', 'bar');
        $this->assertEquals(true, $this->object->hasOption('foo'));
        $this->assertEquals(false, $this->object->hasOption('baz'));
    }

    /**
     * @group unit
     * @small
     * @covers ::deleteOption
     */
    public function testDeleteOption()
    {
        $this->object->addOption('foo', 'bar');
        $this->assertCount(1, $this->object->getOptions());
        $deletedOption = $this->object->deleteOption('foo');
        $this->assertEquals('bar', $deletedOption);
        $this->assertCount(0, $this->object->getOptions());
        $this->assertEquals(null, $this->object->deleteOption('foo'));
        $this->assertEquals(null, $this->object->deleteOption('baz'));
    }
}
