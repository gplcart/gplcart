<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace tests\unit\system\core;

use core\Hook;
use tests\resources\UnitTest;

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
        /* @var $object \core\Hook */
        $this->setInstance('core\\Hook');
    }

    /**
     * @covers core\Hook::getRegistered
     */
    public function testGetRegistered()
    {
        $result = $this->object->getRegistered();
        $this->assertTrue(is_array($result));
    }

    /**
     * @covers core\Hook::getCalled
     */
    public function testGetCalled()
    {
        $result = $this->object->getCalled();
        $this->assertTrue(is_array($result));
    }

    /**
     * @covers core\Hook::modules
     * @todo   Implement testModules().
     */
    public function testModules()
    {
        
    }

    /**
     * @covers core\Hook::register
     * @todo   Implement testRegister().
     */
    public function testRegister()
    {
        
    }

    /**
     * @covers core\Hook::unregister
     * @todo   Implement testUnregister().
     */
    public function testUnregister()
    {
        
    }

    /**
     * @covers core\Hook::fire
     * @todo   Implement testFire().
     */
    public function testFire()
    {
        
    }

    /**
     * @covers core\Hook::fireModule
     * @todo   Implement testFireModule().
     */
    public function testFireModule()
    {
        
    }

}
