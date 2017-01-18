<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\backup;

use gplcart\core\Container;

/**
 * Base backup handler class
 */
class Base
{
    /**
     * Zip helper class instance
     * @var \gplcart\core\helpers\Zip $zip
     */
    protected $zip;

    /**
     * Backup model instance
     * @var \gplcart\core\models\Backup $backup
     */
    protected $backup;

    /**
     * Constructor
     */
    public function __construct()
    {
        ini_set('max_execution_time', 0);

        /* @var $zip \gplcart\core\helpers\Zip */
        $this->zip = Container::get('gplcart\\core\\helpers\\Zip');

        /* @var $backup \gplcart\core\models\Backup */
        $this->backup = Container::get('gplcart\\core\\models\\Backup');
    }

    /**
     * Converts a full path to a relative path
     * @param string $fullpath
     * @return string
     */
    protected function getRelativePath($fullpath)
    {
        return trim(str_replace(GC_FILE_DIR, '', $fullpath), '/');
    }

    /**
     * Creates a unique path for a zip file
     * @param string $id
     * @return string
     */
    protected function getZipPath($id)
    {
        $time = date("y-m-d-i-s");

        $path = GC_PRIVATE_BACKUP_DIR;
        $path .= "/module_{$id}_{$time}.zip";

        return gplcart_file_unique($path);
    }

    /**
     * Gives a temporary unique name to the directory
     * @param string $original
     * @return string|false
     */
    protected function renameTemp($original)
    {
        $temp = gplcart_file_unique("$original--" . uniqid());

        if (rename($original, $temp) === true) {
            return $temp;
        }
        return false;
    }

    /**
     * Deletes a directory and all its content
     * @param string $path
     * @return boolean
     */
    protected function delete($path)
    {
        return gplcart_file_delete_recursive($path);
    }

}
