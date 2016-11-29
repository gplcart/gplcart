<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace tests\unit\system\core;

use core\Container;
use tests\resources\UnitTest;

/**
 * Test cases for DI container class
 */
class ContainerTest extends UnitTest
{

    /**
     * @covers core\Container::instance
     * @group core
     */
    public function testInstance()
    {
        $instance = Container::instance('core\\Facade');
        $this->assertTrue(is_object($instance));
    }

    /**
     * @covers core\Container::registered
     * @depends testInstance
     * @group core
     */
    public function testRegistered()
    {
        Container::instance('core\\Facade');
        $instance = Container::registered('core\\Facade');
        $this->assertTrue(is_object($instance));
    }

    /**
     * @covers core\Container::register
     * @depends testRegistered
     * @group core
     */
    public function testRegister()
    {
        Container::register('test\\Test', (object) array());
        $instance = Container::registered('test\\Test');
        $this->assertTrue(is_object($instance));
    }

    /**
     * @covers core\Container::unregister
     * @depends testRegister
     * @group core
     */
    public function testUnregister()
    {
        Container::register('test\\Test', (object) array());
        Container::unregister('test\\Test');
        $instance = Container::registered('test\\Test');
        $this->assertTrue(empty($instance));
    }

}
