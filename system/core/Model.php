<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Container;

/**
 * Parent class for models
 */
abstract class Model
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Database helper instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hook = Container::get('gplcart\\core\\Hook');
        $this->config = Container::get('gplcart\\core\\Config');
        $this->db = $this->config->getDb();
    }

    /**
     * Returns config instance
     * @return \gplcart\core\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns PDO database instance
     * @return \gplcart\core\Database
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns a property
     * @param string $name
     * @return mixed
     */
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new \InvalidArgumentException("Property $name does not exist");
    }

}
