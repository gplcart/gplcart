<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\search;

use gplcart\core\Container;

/**
 * Base search handler class
 */
class Base
{
    /**
     * Database helper instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Search model instance
     * @var \gplcart\core\models\Search $search
     */
    protected $search;

    /**
     * Config instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        /* @var $search \gplcart\core\models\Search */
        $this->search = Container::get('gplcart\\core\\models\\Search');

        /* @var $config \gplcart\core\Config */
        $this->config = Container::get('gplcart\\core\\Config');

        $this->db = $this->config->getDb();
    }

}
