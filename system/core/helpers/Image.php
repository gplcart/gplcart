<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

use Exception;
use BadMethodCallException;

/**
 * Helper class to work with SimpleImage library
 */
class Image
{

    /**
     * Library class instance
     * @var \abeautifulsite\SimpleImage $lib
     */
    protected $lib;

    /**
     * Constructor
     */
    public function __construct()
    {
        gplcart_require_library('simpleimage/src/abeautifulsite/SimpleImage.php');
    }

    /**
     * Sets a file to be processed
     * @param string $file
     */
    public function set($file)
    {
        try {
            $this->lib = new \abeautifulsite\SimpleImage($file);
            return $this;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Access to library methods
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, array $arguments)
    {
        if (is_callable(array($this->lib, $method))) {
            return call_user_func_array(array($this->lib, $method), $arguments);
        }

        throw new BadMethodCallException("No such method exists: $method");
    }

}
