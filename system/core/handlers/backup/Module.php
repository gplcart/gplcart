<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\backup;

use gplcart\core\handlers\backup\Base as BaseHandler;

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
        $data['type'] = 'module';
        $data['module_id'] = $data['module']['id'];
        $data['version'] = isset($data['module']['version']) ? $data['module']['version'] : '';

        $data['path'] = $this->zip($data);

        if (empty($data['path'])) {
            return false;
        }

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
        $destination = $this->getZipPath($data['module']['id']);
        $result = $this->zip->folder($data['module']['directory'], $destination, $data['module']['id']);

        if ($result === true) {
            return $destination;
        }

        return false;
    }

}
