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
     * @param array $settings
     * @param integer $step
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function install(array $settings, $step, $db)
    {
        $this->db = $db;
        $this->step = $step;
        $this->settings = $settings;

        $this->start();

        $result = $this->process();

        if ($result !== true) {
            return $result;
        }

        $this->finish();

        return array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->getSuccessMessage()
        );
    }

}
