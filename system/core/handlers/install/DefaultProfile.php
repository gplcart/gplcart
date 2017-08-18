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
     * @param \gplcart\core\Database $db
     * @return array
     */
    public function install(array $settings, $db)
    {
        $this->db = $db;
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

    /**
     * Returns success message
     * @return string
     */
    protected function getSuccessMessage()
    {
        if (GC_CLI) {
            $vars = array('@url' => rtrim("{$this->settings['store']['host']}/{$this->settings['store']['basepath']}", '/'));
            return $this->language->text("Your store has been installed.\nURL: @url\nAdmin area: @url/admin\nGood luck!", $vars);
        }

        return $this->language->text('Your store has been installed. Now you can log in as superadmin');
    }

}
