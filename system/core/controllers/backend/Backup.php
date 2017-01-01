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
     * Constructor
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
        $this->downloadBackup();
        $this->actionBackup();

        $query = $this->getFilterQuery();
        $total = $this->getTotalBackup($query);
        $limit = $this->setPager($total, $query);
        $backups = $this->getListBackup($limit, $query);

        $handlers = $this->getHandlersBackup();

        $this->setData('backups', $backups);
        $this->setData('handlers', $handlers);

        $filters = $this->getAllowedFiltersBackup();
        $this->setFilter($filters, $query);

        $this->setTitleListBackup();
        $this->setBreadcrumbListBackup();
        $this->outputListBackup();
    }

    /**
     * Downloads a backup
     * @return null
     */
    protected function downloadBackup()
    {
        $backup_id = $this->request->get('download');

        if (empty($backup_id)) {
            return null;
        }

        $backup = $this->backup->get($backup_id);

        if (empty($backup['path'])) {
            return null;
        }

        $this->response->download(GC_FILE_DIR . "/{$backup['path']}");
        return null;
    }

    /**
     * Applies an action to the selected backups
     * @return null
     */
    protected function actionBackup()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->request->post('selected', array());

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

        return null;
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
     * Renders templates of the backup overview page
     */
    protected function outputListBackup()
    {
        $this->output('tool/backup/list');
    }

    /**
     * Returns an array of allowed filters for list of theme backups
     * @return array
     */
    protected function getAllowedFiltersBackup()
    {
        return array('created', 'name', 'user_id', 'type',
            'version', 'module_id', 'backup_id');
    }

    /**
     * Returns an array of backups
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListBackup(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->backup->getList($query);
    }

    /**
     * Returns total number of backups depending on various conditions
     * @param array $query
     * @return integer
     */
    protected function getTotalBackup(array $query)
    {
        $query['count'] = true;
        return (int) $this->backup->getList($query);
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
