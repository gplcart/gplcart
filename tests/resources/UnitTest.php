<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\resources;

use ReflectionClass;
use ReflectionException;
use PHPUnit_Framework_TestCase;

class UnitTest extends PHPUnit_Framework_TestCase
{

    /**
     * The testing object
     * @var object
     */
    protected $object;

    /**
     * @param null|string $name
     * @param array $data
     * @param string $dname
     */
    public function __construct($name = null, array $data = array(), $dname = '')
    {
        parent::__construct($name, $data, $dname);
    }

    /**
     * Sets the testing object
     * @param string $class
     */
    protected function setInstance($class)
    {
        $this->object = $this->getInstance($class);
    }

    /**
     * Returns a class instance
     * @param string $class
     * @return object
     * @throws \ReflectionException
     */
    protected function getInstance($class)
    {
        if (!class_exists($class)) {
            throw new ReflectionException("Class $class is not callable");
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (empty($constructor)) {
            return new $class;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return new $class;
        }

        $dependencies = array();
        foreach ($parameters as $parameter) {
            $parameter_class = $parameter->getClass();
            if (!empty($parameter_class)) {
                $dependencies[] = $this->getInstance($parameter_class->getName());
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Outputs debugging data
     * @param mixed $data
     */
    protected function dump($data)
    {
        $this->expectOutputString('');
        print_r($data);
    }

}
