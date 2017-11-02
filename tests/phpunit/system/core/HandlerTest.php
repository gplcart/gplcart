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
 * @coversDefaultClass \gplcart\core\Handler
 */
class HandlerTest extends PhpUnitTest
{

    /**
     * @var \gplcart\core\Handler $object
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->tool->getInstance('gplcart\\core\\Handler');
    }

    /**
     * Returns an array of test handlers
     */
    public function handlerDataProvider()
    {
        return array(
            'test_handler_id' => array(
                'handlers' => array(
                    'test_handler' => function() {
                        return true;
                    }
                )
            )
        );
    }

    /**
     * @covers gplcart\core\Handler::call
     */
    public function testCall()
    {
        $data = $this->handlerDataProvider();
        $this->assertTrue($this->object->call($data, 'test_handler_id', 'test_handler'));
        $this->assertTrue($this->object->call($data['test_handler_id'], null, 'test_handler'));
    }

    /**
     * @covers gplcart\core\Handler::get
     */
    public function testGet()
    {
        $data = $this->handlerDataProvider();

        $result1 = $this->object->get($data, 'test_handler_id', 'test_handler');
        $this->assertTrue(is_callable($result1));

        $result2 = $this->object->get($data['test_handler_id'], null, 'test_handler');
        $this->assertTrue(is_callable($result2));
    }

}
