<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\search;

use core\Container;

/**
 * Base search handler class
 */
class Base
{
    /**
     * Database helper instance
     * @var \core\Database $db
     */
    protected $db;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Config instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        /* @var $search \core\models\Search */
        $this->search = Container::instance('core\\models\\Search');

        /* @var $config \core\Config */
        $this->config = Container::instance('core\\Config');

        $this->db = $this->config->getDb();
    }

}
