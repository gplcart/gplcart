<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Backup as BackupModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to backups
 */
class Backup extends BackendController
{

    /**
     * Backup model instance
     * @var \core\models\Backup $backup
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
     * Displays the add backup page
     */
    public function editBackup()
    {
        $modules = $this->getModulesBackup();
        $handlers = $this->getHandlersBackup();

        $this->setData('modules', $modules);
        $this->setData('handlers', $handlers);

        $this->submitEditBackup();

        $this->setTitleEditBackup();
        $this->setBreadcrumbEditBackup();
        $this->outputEditBackup();
    }

    /**
     * Handles an array of submitted data when creating a new backup
     * @return null
     */
    protected function submitEditBackup()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('backup');

        $this->validateEditBackup();

        if (!$this->hasErrors('backup')) {
            $this->addBackup();
        }

        return null;
    }

    /**
     * Validates an array of submitted backup data
     */
    protected function validateEditBackup()
    {
        $this->setSubmitted('user_id', $this->uid);
        $this->validate('backup');
    }

    /**
     * Adds a new backup
     */
    protected function addBackup()
    {
        $submitted = $this->getSubmitted();

        $this->controlAccess($submitted['handler']['access']['backup']);

        $result = $this->backup->backup($submitted['type'], $submitted);

        if (empty($result)) {
            $message = $this->text('An error occurred');
            $this->redirect('', $message, 'warning');
        }

        $vars = array('@url' => $this->url('admin/tool/backup', array('download' => $result)));
        $message = $this->text('Backup has been created. <a href="@url">Download</a>', $vars);
        $this->redirect('admin/tool/backup', $message, 'success');
    }

    /**
     * Returns an array of backup files
     * @return array
     */
    protected function getHandlersBackup()
    {
        return $this->backup->getHandlers();
    }

    /**
     * Returns an array of available modules
     * @return array
     */
    protected function getModulesBackup()
    {
        return $this->config->getModules();
    }

    /**
     * Sets title on the add backup page
     */
    protected function setTitleEditBackup()
    {
        $this->setTitle($this->text('Add backup'));
    }

    /**
     * Sets breadcrumbs on the add backup page
     */
    protected function setBreadcrumbEditBackup()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/tool/backup'),
            'text' => $this->text('Backups')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders templates of the add backup page
     */
    protected function outputEditBackup()
    {
        $this->output('tool/backup/backup');
    }

    /**
     * Displays the restore backup page
     * @param integer $backup_id
     */
    public function editRestoreBackup($backup_id)
    {
        $backup = $this->getBackup($backup_id);

        $this->setData('backup', $backup);

        $this->submitRestoreBackup($backup);

        $this->setBreadcrumbEditRestoreBackup();
        $this->setTitleEditRestoreBackup($backup);
        $this->outputEditRestoreBackup();
    }

    /**
     * Handles an array of submitted data when restoring a backup
     * @param array $backup
     * @return null
     */
    protected function submitRestoreBackup(array $backup)
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->validateRestoreBackup($backup);

        if ($this->hasErrors()) {
            return null;
        }

        $this->restoreBackup($backup);
        return null;
    }

    /**
     * Validates an array of submitted data when restoring a backup
     * @param array $backup
     */
    protected function validateRestoreBackup(array $backup)
    {
        $this->setSubmitted('backup', $backup);
        $this->validate('backup_restore');
    }

    /**
     * Returns a backup data
     * @param integer $backup_id
     * @return array
     */
    protected function getBackup($backup_id)
    {
        $backup = $this->backup->get($backup_id);

        if (empty($backup)) {
            $this->outputError(403);
        }

        return $backup;
    }

    /**
     * Sets breadcrumbs on the restore page
     */
    protected function setBreadcrumbEditRestoreBackup()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/tool/backup'),
            'text' => $this->text('Backups')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the restore page
     * @param array $backup
     */
    protected function setTitleEditRestoreBackup(array $backup)
    {
        $vars = array('%name' => $backup['name']);
        $this->setTitle($this->text('Restore backup %name', $vars));
    }

    /**
     * Renders templates of the restore page
     */
    protected function outputEditRestoreBackup()
    {
        $this->output('tool/backup/restore');
    }

    /**
     * Restores a backup
     * @param array $backup
     */
    protected function restoreBackup(array $backup)
    {
        $submitted = $this->getSubmitted();

        $this->controlAccess($submitted['handler']['access']['restore']);

        $result = $this->backup->restore($backup['type'], $submitted);

        if ($result === true) {
            $message = $this->text('Backup has been restored');
            $this->redirect('admin/tool/backup', $message, 'success');
        }

        if (empty($result)) {
            $message = $this->text('An error occurred');
            $this->redirect('', $message, 'warning');
        }

        if (is_array($result)) {
            $result = end($result);
        }

        $this->redirect('', (string) $result, 'warning');
    }

}
