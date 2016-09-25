<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\File as ModelsFile;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to files
 */
class File extends BackendController
{

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param ModelsFile $file
     */
    public function __construct(ModelsFile $file)
    {
        parent::__construct();

        $this->file = $file;
    }

    /**
     * Displays the file admin overview page
     */
    public function listFile()
    {
        $this->downloadFile();
        $this->actionFile();

        $query = $this->getFilterQuery();
        $total = $this->getTotalFile($query);
        $limit = $this->setPager($total, $query);
        $files = $this->getListFile($limit, $query);

        $this->setData('files', $files);

        $allowed = array('title', 'mime_type', 'file_id', 'created', 'path');
        $this->setFilter($allowed, $query);

        $this->setTitleListFile();
        $this->setBreadcrumbListFile();
        $this->outputListFile();
    }

    /**
     * Returns total number of files depending on the current conditions
     * @param array $query
     */
    protected function getTotalFile(array $query)
    {
        $query['count'] = true;
        return $this->file->getList($query);
    }

    /**
     * Renders the files overview page
     */
    protected function outputListFile()
    {
        $this->output('content/file/list');
    }

    /**
     * Sets titles on the files overview page
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
            'url' => $this->url('admin'));

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Returns an array of files
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListFile(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $files = $this->file->getList($query);

        foreach ($files as &$file) {
            $file['url'] = '';
            // Prevent php errors for invalid/empty paths
            $path = strval(str_replace("\0", "", $file['path']));

            if ($path && file_exists(GC_FILE_DIR . '/' . $path)) {
                $file['url'] = $this->file->url($file['path']);
            }
        }

        return $files;
    }

    /**
     * Applies an action to the selected files
     */
    protected function actionFile()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return;
        }

        $selected = (array) $this->request->post('selected', array());

        $deleted_disk = $deleted_database = 0;

        foreach ($selected as $file_id) {
            if ($action === 'delete' && $this->access('file_delete')) {
                $result = $this->file->deleteAll($file_id);
                $deleted_disk += $result['disk'];
                $deleted_database += $result['database'];
            }
        }

        $message = $this->text('Deleted from database: %db, disk: %disk', array(
            '%db' => $deleted_database, '%disk' => $deleted_disk));

        $this->setMessage($message, 'success', true);
    }

    /**
     * Downloads a file using a file id from the URL
     */
    protected function downloadFile()
    {
        if ($this->isQuery('download')) {

            $file_id = (int) $this->request->get('download');
            $file = $this->file->get($file_id);

            $filepath = GC_FILE_DIR . '/' . $file['path'];
            $this->response->download($filepath);
        }
    }

    /**
     * Displays the file edit page
     * @param null|integer $file_id
     */
    public function editFile($file_id = null)
    {
        $this->downloadFile();
        $file = $this->getFile($file_id);
        $this->submitFile($file);

        $extensions = $this->file->supportedExtensions(true);
        $can_delete = (isset($file['file_id']) && $this->access('file_delete') && $this->file->canDelete($file_id));

        $this->setData('file', $file);
        $this->setData('can_delete', $can_delete);
        $this->setData('extensions', $extensions);

        $this->setTitleEditFile($file);
        $this->setBreadcrumbEditFile($file);
        $this->outputEditFile();
    }

    /**
     * Saves an array of submitted values
     * @param array $file
     */
    protected function submitFile(array $file)
    {
        if ($this->isPosted('delete') && isset($file['file_id'])) {
            return $this->deleteFile($file);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('file');
        $this->validateFile($file);

        if ($this->hasErrors('file')) {
            return;
        }

        if (isset($file['file_id'])) {
            return $this->updateFile($file);
        }

        $this->addFile();
    }

    /**
     * Validates a submitted data
     * @param array $file
     */
    protected function validateFile(array $file)
    {
        $this->addValidator('title', array(
            'length' => array('max' => 255)
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        if (empty($file['file_id'])) {
            $this->addValidator('file', array(
                'upload' => array(
                    'required' => true,
                    'control_errors' => true,
                    'path' => 'image/upload/common',
                    'file' => $this->request->file('file')
            )));
        }

        $errors = $this->setValidators($file);

        if (empty($errors) && empty($file['file_id'])) {
            $uploaded = $this->getValidatorResult('file');
            $this->setSubmitted('path', $uploaded);
        }
    }

    /**
     * Completely deletes a file from the database an disk
     * @param array $file
     */
    protected function deleteFile(array $file)
    {
        $this->controlAccess('file_delete');
        $result = $this->file->deleteAll($file['file_id']);

        if (array_sum($result) === 2) {
            $message = $this->text('File has been deleted from database and disk');
            $this->redirect('admin/content/file', $message, 'success');
        }

        $message = $this->text('An error occurred while deleting the file');
        $this->redirect('admin/content/file', $message, 'warning');
    }

    /**
     * Updates a file with submitted values
     * @param array $file
     */
    protected function updateFile(array $file)
    {
        $this->controlAccess('file_edit');
        $submitted = $this->getSubmitted();
        $updated = $this->file->update($file['file_id'], $submitted);

        if ($updated) {
            $message = $this->text('File has been updated');
            $this->redirect('admin/content/file', $message, 'success');
        }

        $message = $this->text('File has not been updated');
        $this->redirect('admin/content/file', $message, 'warning');
    }

    /**
     * Adds a new file using an array of submitted values
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
     * @param array $file
     */
    protected function setTitleEditFile(array $file)
    {
        if (isset($file['file_id'])) {
            $title = $this->text('Edit file %title', array('%title' => $file['title']));
        } else {
            $title = $this->text('Add file');
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
     * Renders the edit file page
     */
    protected function outputEditFile()
    {
        $this->output('content/file/edit');
    }

    /**
     * Returns an array of file data
     * @param integer $file_id
     * @return array
     */
    protected function getFile($file_id)
    {
        if (!is_numeric($file_id)) {
            return array();
        }

        $file = $this->file->get($file_id);

        if (empty($file)) {
            $this->outputError(404);
        }

        return $file;
    }

}
