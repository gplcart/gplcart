<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

use ReflectionClass;
use ReflectionException;

/**
 * Contains various helper methods
 */
class Tool
{

    /**
     * Returns a class instance
     * @param string $class
     * @return object
     * @throws \ReflectionException
     */
    public function getInstance($class)
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
            $dependencies[] = $this->getInstance($parameter_class->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Returns a random string
     * @param int $length
     * @return string
     */
    public function getRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);

        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, $characters_length - 1)];
        }

        return $random_string;
    }

}
