<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\backup;

use core\handlers\backup\Base as BaseHandler;

/**
 * Provides methods to backup/restore modules
 */
class Module extends BaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates a module backup
     * @param array $data
     * @return integer
     */
    public function backup(array $data)
    {
        $data['path'] = $this->zip($data);

        if (empty($data['path'])) {
            return false;
        }

        return $this->add($data);
    }

    /**
     * Adds a backup record to the database
     * @param array $data
     * @return integer
     */
    protected function add(array $data)
    {
        $data['version'] = $data['module']['version'];
        $data['path'] = $this->getRelativePath($data['path']);

        return $this->backup->add($data);
    }

    /**
     * Adds module files to an archive
     * @param array $data
     * @return boolean|string A path to the created archive
     */
    protected function zip(array $data)
    {
        $source = "{$data['module']['directory']}/*";
        $destination = $this->getZipPath($data['module_id']);
        $result = $this->zip->folder($source, $destination, $data['module_id']);

        if ($result === true) {
            return $destination;
        }

        return false;
    }

    /**
     * Restores a module
     * @param array $data
     * @return boolean
     */
    public function restore(array $data)
    {
        // Rename an existing folder to avoid collision
        $temppath = $this->renameTemp($data['module']['directory']);

        if (empty($temppath)) {
            return false; // Cannot rename, exit
        }

        // Extract zipped content
        $zippath = GC_FILE_DIR . "/{$data['backup']['path']}";
        $extracted = $this->zip->set($zippath)->extract(GC_MODULE_DIR);

        if ($extracted) {
            // Success. Remove old folder
            $this->delete($temppath);
            return true;
        }

        // Error. Roll back changes
        // Delete a directory that can be created after extraction
        $this->delete($data['module']['directory']);

        // Restore original folder name
        rename($temppath, $data['module']['directory']);
        return false;
    }

}
