<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\install;

use gplcart\core\handlers\install\Base as BaseInstall;

/**
 * Default installer
 */
class DefaultProfile extends BaseInstall
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Performs full system installation
     * @param array $data
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function install(array $data, $db)
    {
        $this->db = $db;
        $this->data = $data;

        $this->start();
        $result = $this->process();

        if ($result !== true) {
            return $result;
        }

        return $this->finish();
    }

}
