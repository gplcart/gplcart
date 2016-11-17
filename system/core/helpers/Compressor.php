<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

use BadMethodCallException;

/**
 * Wrapper class for minifier library
 */
class Compressor
{

    /**
     * CSS minifier instance
     * @var \MatthiasMullie\Minify\CSS $css
     */
    protected $css;

    /**
     * JS minifier instance
     * @var \MatthiasMullie\Minify\JS $js
     */
    protected $js;

    /**
     * Includes all required files and inits objects
     */
    public function __construct()
    {
        require_once GC_LIBRARY_DIR . '/minify/src/Minify.php';
        require_once GC_LIBRARY_DIR . '/minify/src/CSS.php';
        require_once GC_LIBRARY_DIR . '/minify/src/JS.php';
        require_once GC_LIBRARY_DIR . '/minify/src/Exception.php';
        require_once GC_LIBRARY_DIR . '/minify/src/Exceptions/BasicException.php';
        require_once GC_LIBRARY_DIR . '/minify/src/Exceptions/FileImportException.php';
        require_once GC_LIBRARY_DIR . '/minify/src/Exceptions/IOException.php';
        require_once GC_LIBRARY_DIR . '/minify/path-converter/src/Converter.php';

        $this->js = new \MatthiasMullie\Minify\JS;
        $this->css = new \MatthiasMullie\Minify\CSS;
    }

    /**
     * Calls a JS related method
     * @param string $method
     * @param array|string $argument
     * @return mixed
     * @throws BadMethodCallException
     */
    public function js($method, $argument)
    {
        $arguments = (array) $argument;

        if (is_callable(array($this->js, $method))) {
            return call_user_func_array(array($this->js, $method), $arguments);
        }

        throw new BadMethodCallException("No such method exists: $method");
    }

    /**
     * Calls a CSS related method
     * @param string $method
     * @param array|string $argument
     * @return mixed
     * @throws BadMethodCallException
     */
    public function css($method, $argument)
    {
        $arguments = (array) $argument;

        if (is_callable(array($this->css, $method))) {
            return call_user_func_array(array($this->css, $method), $arguments);
        }

        throw new BadMethodCallException("No such method exists: $method");
    }

}
