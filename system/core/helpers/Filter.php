<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Provides methods to work with HtmlPurifier
 */
class Filter
{

    /**
     * The current HTMLPurifier instance
     * @var \HTMLPurifier $lib
     */
    protected $lib;

    /**
     * Array of initialized instances
     * @var array
     */
    protected $instances = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        gplcart_require_library('htmlpurifier/library/HTMLPurifier.auto.php');
    }

    /**
     * Filters a text string
     * @param string $text
     * @param array $config
     * @return string
     */
    public function filter($text, array $config = array())
    {
        $key = md5(json_encode($config));

        if (isset($this->instances[$key])) {
            $this->lib = $this->instances[$key];
            return $this->lib->purify($text);
        }

        if (empty($config)) {
            $config = \HTMLPurifier_Config::createDefault();
        } else {
            $config = \HTMLPurifier_Config::create($config);
        }

        $this->lib = new \HTMLPurifier($config);
        $this->instances[$key] = $this->lib;

        return $this->lib->purify($text);
    }

    /**
     * Access to HTMLPurifier methods
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
