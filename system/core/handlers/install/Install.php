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
class Install extends BaseInstall
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
    public function process(array $data, \gplcart\core\Database $db)
    {
        $this->db = $db;

        $this->start($data);

        if (!$this->createDefaultDb()) {
            return array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->language->text('Failed to create all necessary tables in the database')
            );
        }

        if (!$this->createDefaultConfig($data)) {
            return array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->language->text('Failed to create config.php')
            );
        }

        $result = $this->setDefaultStore($data);

        if ($result !== true) {
            return array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => (string) $result
            );
        }

        $this->finish();

        return array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->language->text('Your store has been installed. Now you can log in as superadmin')
        );
    }

}
