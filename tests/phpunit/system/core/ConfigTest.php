<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\system\core;

use gplcart\tests\phpunit\support\PhpUnitTest;

/**
 * @coversDefaultClass \gplcart\core\Config
 */
class ConfigTest extends PhpUnitTest
{

    /**
     * Object class instance
     * @var \gplcart\core\Config $object
     */
    protected $object;

    /**
     * Fixture name
     */
    const FIXTURE = 'settings';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->tool->getInstance('gplcart\\core\\Config');

        $this->setFixtures('settings');
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers gplcart\core\Config::setDb
     */
    public function testSetDb()
    {
        $this->assertFixtureTable(self::FIXTURE);
        $this->assertTrue(is_object($this->object->setDb(static::$pdo)));
    }

    /**
     * @covers gplcart\core\Config::getDb
     * @depends testSetDb
     */
    public function testGetDb()
    {
        $this->object->setDb(static::$pdo);
        $this->assertTrue(is_object($this->object->getDb()));
    }

    /**
     * @covers gplcart\core\Config::select
     * @depends testSetDb
     */
    public function testSelect()
    {
        $this->object->setDb(static::$pdo);

        $actual = $this->object->select();
        $this->assertInternalType('array', $actual);
        $this->assertCount(2, $actual);

        $this->assertTrue($this->object->select('test_1') === 'test_value_1');
        $this->assertArrayHasKey('test', $this->object->select('test_2'));
        $this->assertEquals('some_default_value', $this->object->select('some_fake_value', 'some_default_value'));
    }

    /**
     * @covers gplcart\core\Config::set
     * @depends testSetDb
     * @depends testSelect
     */
    public function testSet()
    {
        $key = 'some_test_key';
        $value = 'some_test_value';

        $this->object->setDb(static::$pdo);

        $this->object->set($key, $value);
        $this->assertEquals($value, $this->object->select($key));

        $this->object->set($key, array($key => true));
        $this->assertArrayHasKey($key, $this->object->select($key));
    }

    /**
     * @covers gplcart\core\Config::reset
     * @depends testSet
     * @depends testSetDb
     * @depends testSelect
     */
    public function testReset()
    {
        $key = 'some_test_key';
        $value = 'some_test_value';

        $this->object->setDb(static::$pdo);
        $this->object->set($key, $value);

        $this->assertTrue($this->object->reset($key));
        $this->assertEmpty($this->object->select($key));
    }

    /**
     * @covers gplcart\core\Config::setKey
     */
    public function testSetKey()
    {
        $expected = $this->tool->getRandomString();
        $this->assertEquals($expected, $this->object->setKey($expected));
    }

    /**
     * @covers gplcart\core\Config::getKey
     * @depends testSetKey
     */
    public function testGetKey()
    {
        $expected = $this->tool->getRandomString();
        $this->object->setKey($expected);
        $this->assertEquals($expected, $this->object->getKey());
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

    /**
     * @covers gplcart\core\Config::getToken
     */
    public function testGetToken()
    {
        $this->assertInternalType('string', $this->object->getToken());
    }

}
