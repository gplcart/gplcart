<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Container;

/**
 * Parent class for models
 */
class Model
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Database helper instance
     * @var \core\helpers\Database $db
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        /* @var $hook \core\Hook */
        $this->hook = Container::instance('core\\Hook');

        /* @var $config \core\Config */
        $this->config = Container::instance('core\\Config');

        /* @var $db \core\helpers\Database */
        $this->db = $this->config->getDb();
    }

    /**
     * Returns config instance
     * @return \core\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns PDO database instance
     * @return \core\helpers\Database
     */
    public function getDb()
    {
        return $this->db;
    }

}
