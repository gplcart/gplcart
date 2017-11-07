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
 * @coversDefaultClass \gplcart\core\Hook
 */
class HookTest extends UnitTest
{

    /**
     * Object class instance
     * @var \gplcart\core\Hook $object
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $config = array(
            'gplcart\\core\\Config' => array(
                'methods' => array(
                    'getEnabledModules' => array('return' => array())
                )
            )
        );

        $this->object = $this->getInstance('gplcart\\core\\Hook', $config);
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->object = null;
        parent::tearDown();
    }

    /**
     * @covers gplcart\core\Hook::registerAll
     */
    public function testRegisterAll()
    {
        $this->assertTrue(is_array($this->object->registerAll()));
    }

    /**
     * @covers gplcart\core\Hook::register
     */
    public function testRegister()
    {
        $method = 'Test';
        $class = 'test\\Test';
        $result = $this->object->register($method, $class);

        $expected = array(
            strtolower($method) => array(
                $class => array($class, $method)));

        $this->assertEquals($expected, $result);
    }

}
