<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Config,
    gplcart\core\Hook;

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
     * @param Config $config
     * @param Hook $hook
     */
    public function __construct(Config $config, Hook $hook)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();
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
