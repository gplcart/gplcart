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

        $result_db = $this->createDb();

        if ($result_db !== true) {
            return array('redirect' => '', 'severity' => 'warning', 'message' => $result_db);
        }

        $result_config = $this->createConfig($data);

        if ($result_config !== true) {
            return array('redirect' => '', 'severity' => 'warning', 'message' => $result_config);
        }

        $result_store = $this->setStore($data);

        if ($result_store !== true) {
            return array('redirect' => '', 'severity' => 'warning', 'message' => $result_store);
        }

        $this->finish();

        return array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->getSuccessMessage($data)
        );
    }

    /**
     * Returns success message
     * @param array $data
     * @return string
     */
    protected function getSuccessMessage(array $data)
    {
        if (GC_CLI) {
            $vars = array('@url' => rtrim("{$data['store']['host']}/{$data['store']['basepath']}", '/'));
            return $this->language->text("Your store has been installed.\nURL: @url\nAdmin area: @url/admin\nGood luck!", $vars);
        }

        return $this->language->text('Your store has been installed. Now you can log in as superadmin');
    }

}
