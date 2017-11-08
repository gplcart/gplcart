<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\system\core;

use gplcart\tests\phpunit\support\UnitTest;

/**
 * @coversDefaultClass \gplcart\core\Config
 */
class ConfigTest extends UnitTest
{

    /**
     * Object class instance
     * @var \gplcart\core\Config $object
     */
    protected $object;

    /**
     * An array of fixture data
     * @var array
     */
    protected $fixture_data;

    /**
     * Returns an array of configuration used to mock the testing object
     * @return array
     */
    protected function getMockConfig()
    {
        return array('gplcart\\core\\Database' => $this->getSystemDatabase());
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->fixture_data = $this->getFixtureData('settings');
        $this->object = $this->getInstance('gplcart\\core\\Config', $this->getMockConfig());
        parent::setUp();
    }

    /**
     * @covers gplcart\core\Config::select
     */
    public function testSelect()
    {
        $actual = $this->object->select();
        $this->assertInternalType('array', $actual);
        $this->assertCount(count($this->fixture_data), $actual);

        $this->assertTrue($this->object->select('test_1') === 'test_value_1');
        $this->assertArrayHasKey('test', $this->object->select('test_2'));
        $this->assertEquals('some_default_value', $this->object->select('some_fake_value', 'some_default_value'));
    }

    /**
     * @covers gplcart\core\Config::set
     */
    public function testSet()
    {
        $key = 'some_test_key';
        $value = 'some_test_value';

        $this->object->set($key, $value);
        $this->assertEquals($value, $this->object->select($key));

        $this->object->set($key, array($key => true));
        $this->assertArrayHasKey($key, $this->object->select($key));
    }

    /**
     * @covers gplcart\core\Config::reset
     */
    public function testReset()
    {
        $first = reset($this->fixture_data);

        $this->assertDbRecordExists('settings', 'id', $first['id']);
        $this->assertTrue($this->object->reset($first['id']));
        $this->assertDbRecordNotExists('settings', 'id', $first['id']);
    }

    /**
     * @covers gplcart\core\Config::isValidModuleId
     */
    public function testIsValidModuleId()
    {
        $invalid = array('test module', '2test_module', 'test,module', '1234', 'gplcart', 'core');

        foreach ($invalid as $string) {
            $this->assertFalse($this->object->isValidModuleId($string));
        }

        $valid = array('testmodule', 'test_module', 'test_module2');

        foreach ($valid as $string) {
            $this->assertTrue($this->object->isValidModuleId($string));
        }
    }

}
