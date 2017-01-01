<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\unit\system\core;

use gplcart\tests\resources\UnitTest;

/**
 * Test cases for Hook class
 */
class HookTest extends UnitTest
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        /* @var $object \gplcart\core\Hook */
        $this->setInstance('gplcart\\core\\Hook');
    }

    /**
     * @covers gplcart\core\Hook::getRegistered
     */
    public function testGetRegistered()
    {
        $result = $this->object->getRegistered();
        $this->assertTrue(is_array($result));
    }

    /**
     * @covers gplcart\core\Hook::getCalled
     */
    public function testGetCalled()
    {
        $result = $this->object->getCalled();
        $this->assertTrue(is_array($result));
    }

    /**
     * @covers gplcart\core\Hook::register
     */
    public function testRegister()
    {
        $method = 'hookTest';
        $class = 'test\\Test';
        $key_method = strtolower($method);

        $result = $this->object->register($method, $class);

        $this->assertTrue(isset($result[$key_method][$class]));
        $this->assertTrue(is_array($result[$key_method][$class]));
        $this->assertEquals(2, count($result[$key_method][$class]));

        list($registered_class, $registered_method) = $result[$key_method][$class];

        $this->assertEquals($class, $registered_class);
        $this->assertEquals($method, $registered_method);
    }

    /**
     * @covers gplcart\core\Hook::unregister
     * @depends testRegister
     */
    public function testUnregister()
    {
        $method = 'hookTest';
        $class = 'test\\Test';

        $this->object->register($method, $class);
        $result = $this->object->unregister($method, $class);

        $this->assertTrue(is_array($result));
        $this->assertTrue(!isset($result[strtolower($method)][$class]));
    }

    /**
     * @covers gplcart\core\Hook::getMethod
     */
    public function testGetMethod()
    {
        $hook = 'Test.Hook.name';
        $result = $this->object->getMethod($hook);

        $this->assertEquals(0, strpos($result, 'hook'));
        $this->assertEquals(1, preg_match('/^[a-z0-9]+$/', $result));
    }

}
