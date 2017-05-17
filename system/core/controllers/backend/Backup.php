<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Backup as BackupModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to backups
 */
class Backup extends BackendController
{

    /**
     * Backup model instance
     * @var \gplcart\core\models\Backup $backup
     */
    protected $backup;

    /**
     * @param BackupModel $backup
     */
    public function __construct(BackupModel $backup)
    {
        parent::__construct();

        $this->backup = $backup;
    }

    /**
     * Displays the backup overview page
     */
    public function listBackup()
    {
        $this->downloadListBackup();
        $this->actionListBackup();

        $this->setTitleListBackup();
        $this->setBreadcrumbListBackup();

        $this->setFilterListBackup();
        $this->setTotalListBackup();
        $this->setPagerLimit();

        $this->setData('handlers', $this->getHandlersBackup());
        $this->setData('backups', $this->getListBackup());

        $this->outputListBackup();
    }

    /**
     * Sets filter parameters
     */
    protected function setFilterListBackup()
    {
        $allowed = array('created', 'name', 'user_id', 'type',
            'version', 'module_id', 'backup_id');
        $this->setFilter($allowed);
    }

    /**
     * Downloads a backup
     */
    protected function downloadListBackup()
    {
        $backup_id = $this->getQuery('download');

        if (empty($backup_id)) {
            return null;
        }

        $this->controlAccess('backup_download');
        $backup = $this->backup->get($backup_id);

        if (!empty($backup['path'])) {
            $this->download(GC_FILE_DIR . "/{$backup['path']}");
        }
    }

    /**
     * Applies an action to the selected backups
     */
    protected function actionListBackup()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->getPosted('selected', array());

        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('backup_delete')) {
                $deleted += (int) $this->backup->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Backups have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets title on the backup overview page
     */
    protected function setTitleListBackup()
    {
        $this->setTitle($this->text('Backups'));
    }

    /**
     * Sets breadcrumbs on the backup overview page
     */
    protected function setBreadcrumbListBackup()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the backup overview page
     */
    protected function outputListBackup()
    {
        $this->output('tool/backup/list');
    }

    /**
     * Returns an array of backups
     * @return array
     */
    protected function getListBackup()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        return $this->backup->getList($query);
    }

    /**
     * Sets a total number of backups depending on the filter conditions
     */
    protected function setTotalListBackup()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->backup->getList($query);
    }

    /**
     * Returns an array of backup files
     * @return array
     */
    protected function getHandlersBackup()
    {
        return $this->backup->getHandlers();
    }

}
