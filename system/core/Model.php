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
class Model
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
        /* @var $hook \gplcart\core\Hook */
        $this->hook = Container::get('gplcart\\core\\Hook');

        /* @var $config \gplcart\core\Config */
        $this->config = Container::get('gplcart\\core\\Config');

        /* @var $db \gplcart\core\Database */
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

}
