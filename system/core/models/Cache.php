<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Handler;

/**
 * Manages basic behaviors and data related to cache system
 */
class Cache extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets a cache data
     * @param string $key
     * @param mixed $data
     * @param array $options
     * @return boolean
     */
    public function set($key, $data, $options = array())
    {
        $options += array('storage' => 'file', 'lifespan' => 0);

        $handlers = $this->getHandlers();
        return Handler::call($handlers, $options['storage'], 'set', array($key, $data, $options));
    }

    /**
     * Returns a cached data
     * @param string $key
     * @param array $options
     * @return mixed
     */
    public function get($key, $options = array())
    {
        $options += array('storage' => 'file', 'default' => null);

        $handlers = $this->getHandlers();
        return Handler::call($handlers, $options['storage'], 'get', array($key, $options));
    }

    /**
     * Clears a cached data
     * @param string $key
     * @param array $options
     * @return boolean
     */
    public function clear($key, $options = array())
    {
        $options += array('storage' => 'file');

        $handlers = $this->getHandlers();
        return Handler::call($handlers, $options['storage'], 'clear', array($key, $options));
    }

    /**
     * Returns an array of cache handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_cache('cache.handles');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = $this->getDefaultHandlers();
        $this->hook->fire('cache.handlers', $handlers);
        return $handlers;
    }

    /**
     * Returns an array of default cache handlers
     * @return array
     */
    protected function getDefaultHandlers()
    {
        $handlers = array();

        $handlers['file'] = array(
            'handlers' => array(
                'set' => array('core\\handlers\\cache\\File', 'set'),
                'get' => array('core\\handlers\\cache\\File', 'get'),
                'clear' => array('core\\handlers\\cache\\File', 'clear')
        ));

        return $handlers;
    }

}
