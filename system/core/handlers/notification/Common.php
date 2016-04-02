<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\notification;

use core\classes\Tool;

class Common
{

    /**
     * Checks system directories and files
     * and returns an array of notifications if at least one error occurred
     * @param array $parameters
     * @return boolean|array
     */
    public function system()
    {
        $notifications[] = $this->checkPermissions(GC_CONFIG_COMMON);

        if (file_exists(GC_CONFIG_OVERRIDE)) {
            $notifications[] = $this->checkPermissions(GC_CONFIG_OVERRIDE);
        }

        $notifications[] = $this->checkHtaccess(GC_ROOT_DIR);
        $notifications[] = $this->checkHtaccess(GC_CACHE_DIR);

        // false - do not add "Deny from all" as it public directory
        $notifications[] = $this->checkHtaccess(GC_FILE_DIR, false);
        $notifications[] = $this->checkHtaccess(GC_PRIVATE_DIR);

        $notifications = array_filter($notifications, 'is_array');

        if (!$notifications) {
            return false;
        }

        return array(
            'summary' => array('message' => 'Security issue', 'severity' => 'warning'),
            'messages' => $notifications,
            'weight' => -99,
        );
    }

    /**
     * Checks file permissions
     * @param string $file
     * @param string $permissions
     * @return boolean|array
     */
    protected function checkPermissions($file, $permissions = '0444')
    {
        if (substr(sprintf('%o', fileperms($file)), -4) === (string) $permissions) {
            return true;
        }

        return array(
            'message' => 'File %s is not secure. File permissions must be %perm',
            'variables' => array('%s' => $file, '%perm' => $permissions),
            'severity' => 'warning',
        );
    }

    /**
     * Checks permissions and existance of .htaccess file
     * @param string $directory
     * @param boolean $private
     * @return boolean|array
     */
    protected function checkHtaccess($directory, $private = true)
    {
        $htaccess = $directory . '/.htaccess';

        if (file_exists($htaccess)) {
            return $this->checkPermissions($htaccess);
        }

        // Try to create the missing file
        if (Tool::htaccess($directory, $private)) {
            return true;
        }

        return array(
            'message' => 'Missing .htaccess file %s',
            'variables' => array('%s' => $htaccess),
            'severity' => 'danger'
        );
    }
}
