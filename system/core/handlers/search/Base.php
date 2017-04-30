<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\search;

use gplcart\core\Container,
    gplcart\core\Handler;

/**
 * Base search handler class
 */
class Base extends Handler
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->db = $this->config->getDb();
        $this->search = Container::get('gplcart\\core\\models\\Search');
    }

}
