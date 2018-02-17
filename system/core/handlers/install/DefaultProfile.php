<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\install;

use Exception;

/**
 * Default installer
 */
class DefaultProfile extends Base
{
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

        try {
            $this->start();
            $this->process();
            return $this->finish();
        } catch (Exception $ex) {
            return array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $ex->getMessage()
            );
        }
    }

}
