<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\File as FileModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to files
 */
class File extends BackendController
{

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * The current file
     * @var array
     */
    protected $data_file = array();

    /**
     * An array of filter parameters
     * @var array
     */
    protected $data_filter = array();

    /**
     * Pager limits
     * @var array
     */
    protected $data_limit;

    /**
     * A total number of items found for the filter conditions
     * @var integer
     */
    protected $data_total;

    /**
     * @param FileModel $file
     */
    public function __construct(FileModel $file)
    {
        parent::__construct();

        $this->file = $file;
    }

    /**
     * Displays the file overview page
     */
    public function listFile()
    {
        $this->downloadFile();
        $this->actionListFile();

        $this->setTitleListFile();
        $this->setBreadcrumbListFile();

        $this->setFilterListFile();
        $this->setTotalListFile();
        $this->setPagerListFile();

        $this->setData('files', $this->getListFile());
        $this->outputListFile();
    }

    /**
     * Set filter on the file overview page
     */
    protected function setFilterListFile()
    {
        $this->data_filter = $this->getFilterQuery();
        $allowed = array('title', 'mime_type', 'file_id', 'created', 'path');
        $this->setFilter($allowed, $this->data_filter);
    }

    /**
     * Set pager on the file overview page
     */
    protected function setPagerListFile()
    {
        $this->data_limit = $this->setPager($this->data_total, $this->data_filter);
    }

    /**
     * Downloads a file
     */
    protected function downloadFile()
    {
        $file_id = (int) $this->getQuery('download');

        if (!empty($file_id)) {
            $file = $this->file->get($file_id);
            $filepath = GC_FILE_DIR . '/' . $file['path'];
            $this->download($filepath);
        }
    }

    /**
     * Applies an action to the selected files
     */
    protected function actionListFile()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->getPosted('selected', array());

        $deleted_disk = $deleted_database = 0;

        foreach ($selected as $file_id) {
            if ($action === 'delete' && $this->access('file_delete')) {
                $result = $this->file->deleteAll($file_id);
                $deleted_disk += $result['disk'];
                $deleted_database += $result['database'];
            }
        }

        $vars = array('%db' => $deleted_database, '%disk' => $deleted_disk);
        $message = $this->text('Deleted from database: %db, disk: %disk', $vars);
        $this->setMessage($message, 'success', true);
    }

    /**
     * Set a total number of files depending on the filter conditions
     */
    protected function setTotalListFile()
    {
        $query = $this->data_filter;
        $query['count'] = true;
        $this->data_total = (int) $this->file->getList($query);
    }

    /**
     * Returns an array of files
     * @return array
     */
    protected function getListFile()
    {
        $query = $this->data_filter;
        $query['limit'] = $this->data_limit;
        $files = (array) $this->file->getList($query);
        return $this->prepareListFile($files);
    }

    /**
     * Prepare an array of files
     * @param array $files
     * @return array
     */
    protected function prepareListFile(array $files)
    {
        foreach ($files as &$file) {
            // Prevent php errors for invalid/empty paths
            $path = strval(str_replace("\0", "", $file['path']));
            $file['url'] = '';
            if ($path && file_exists(GC_FILE_DIR . '/' . $path)) {
                $file['url'] = $this->file->url($file['path']);
            }
        }
        return $files;
    }

    /**
     * Sets title on the files overview page
     */
    protected function setTitleListFile()
    {
        $this->setTitle($this->text('Files'));
    }

    /**
     * Sets breadcrumbs on the files overview page
     */
    protected function setBreadcrumbListFile()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the file overview page
     */
    protected function outputListFile()
    {
        $this->output('content/file/list');
    }

    /**
     * Displays the file edit page
     * @param null|integer $file_id
     */
    public function editFile($file_id = null)
    {
        $this->downloadFile();
        $this->setFile($file_id);

        $this->setTitleEditFile();
        $this->setBreadcrumbEditFile();

        $this->controlAccessEditFile();

        $this->setData('file', $this->data_file);
        $this->setData('can_delete', $this->canDeleteFile());
        $this->setData('extensions', $this->file->supportedExtensions(true));

        $this->submitEditFile();
        $this->outputEditFile();
    }

    /**
     * Controls access to the edit file page
     */
    protected function controlAccessEditFile()
    {
        if (empty($this->data_file['file_id'])) {
            $this->controlAccess('file_upload');
        }
    }

    /**
     * Whether the file can be deleted
     * @return bool
     */
    protected function canDeleteFile()
    {
        return isset($this->data_file['file_id'])//
                && $this->access('file_delete')//
                && $this->file->canDelete($this->data_file['file_id']);
    }

    /**
     * Sets a file data
     * @param integer $file_id
     */
    protected function setFile($file_id)
    {
        if (is_numeric($file_id)) {
            $file = $this->file->get($file_id);

            if (empty($file)) {
                $this->outputHttpStatus(404);
            }

            $this->data_file = $file;
        }
    }

    /**
     * Saves an array of submitted values
     * @return null
     */
    protected function submitEditFile()
    {
        if ($this->isPosted('delete') && isset($this->data_file['file_id'])) {
            $this->deleteFile();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditFile()) {
            return null;
        }

        if (isset($this->data_file['file_id'])) {
            $this->updateFile();
        } else {
            $this->addFile();
        }
    }

    /**
     * Deletes a file from the database an disk
     */
    protected function deleteFile()
    {
        $this->controlAccess('file_delete');
        $result = $this->file->deleteAll($this->data_file['file_id']);

        if (array_sum($result) === 2) {
            $message = $this->text('File has been deleted from database and disk');
            $this->redirect('admin/content/file', $message, 'success');
        }

        $message = $this->text('An error occurred while deleting the file');
        $this->redirect('admin/content/file', $message, 'warning');
    }

    /**
     * Validates a submitted data
     */
    protected function validateEditFile()
    {
        $this->setSubmitted('file');
        $this->setSubmitted('update', $this->data_file);

        $this->validateComponent('file');
        return !$this->hasErrors();
    }

    /**
     * Updates a file
     */
    protected function updateFile()
    {
        $this->controlAccess('file_edit');

        $submitted = $this->getSubmitted();
        $updated = $this->file->update($this->data_file['file_id'], $submitted);

        if ($updated) {
            $message = $this->text('File has been updated');
            $this->redirect('admin/content/file', $message, 'success');
        }

        $message = $this->text('File has not been updated');
        $this->redirect('admin/content/file', $message, 'warning');
    }

    /**
     * Adds a new file
     */
    protected function addFile()
    {
        $this->controlAccess('file_add');

        $submitted = $this->getSubmitted();
        $result = $this->file->add($submitted);

        if (empty($result)) {
            $message = $this->text('File has not been added');
            $this->redirect('admin/content/file', $message, 'warning');
        }

        $message = $this->text('File has been added');
        $this->redirect('admin/content/file', $message, 'success');
    }

    /**
     * Sets titles on the edit file page
     */
    protected function setTitleEditFile()
    {
        $title = $this->text('Add file');

        if (isset($this->data_file['file_id'])) {
            $title = $this->text('Edit file %name', array('%name' => $this->data_file['title']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit file page
     */
    protected function setBreadcrumbEditFile()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/file'),
            'text' => $this->text('Files')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit file page
     */
    protected function outputEditFile()
    {
        $this->output('content/file/edit');
    }

}
