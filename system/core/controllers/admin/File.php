<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use Core\Controller;
use core\models\File as ModelsFile;

/**
 * Handles incoming requests and outputs data related to files
 */
class File extends Controller
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
    public function files()
    {
        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->getTotalFiles($query), $query);

        $this->data['files'] = $this->getFiles($limit, $query);

        $allowed = array('title', 'mime_type', 'created', 'path');
        $this->setFilter($allowed, $query);

        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action);
        }

        $this->setTitleFiles();
        $this->setBreadcrumbFiles();
        $this->outputFiles();
    }

    /**
     * Displays the edit file form
     * @param integer|null $file_id
     */
    public function edit($file_id = null)
    {
        $file = $this->get($file_id);

        $supported_extensions = $this->file->supportedExtensions(true);
        $this->data['supported_extensions'] = implode(',', $supported_extensions);
        $this->data['file'] = $file;

        if ($this->request->post('delete')) {
            $this->delete($file);
        }

        if ($this->request->post('save')) {
            $this->submit($file);
        }

        $this->setTitleEdit($file);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Returns total number of files for pager
     * @param array $query
     */
    protected function getTotalFiles(array $query)
    {
        return $this->file->getList(array('count' => true) + $query);
    }

    /**
     * Renders the files overview page
     */
    protected function outputFiles()
    {
        $this->output('content/file/list');
    }

    /**
     * Sets titles on the files overview page
     */
    protected function setTitleFiles()
    {
        $this->setTitle($this->text('Files'));
    }

    /**
     * Sets breadcrumbs on the files overview page
     */
    protected function setBreadcrumbFiles()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Returns an array of files
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getFiles(array $limit, array $query)
    {
        $files = $this->file->getList(array('limit' => $limit) + $query);

        foreach ($files as &$file) {
            $path = strval(str_replace("\0", "", $file['path'])); // Prevent php errors for invalid/empty paths
            $file['url'] = ($path && file_exists(GC_FILE_DIR . '/' . $path)) ? $this->file->url($file['path']) : '';
        }

        return $files;
    }

    /**
     * Applies an action to the selected files
     * @param array $selected
     * @param string $action
     * @return boolean
     */
    protected function action(array $selected, $action)
    {
        $deleted_disk = $deleted_database = 0;

        foreach ($selected as $file_id) {
            if (!in_array($action, array('delete', 'delete_both'))) {
                continue;
            }

            if (!$this->access('file_delete')) {
                continue;
            }

            $file = $this->file->get($file_id);
            $success = $this->file->delete($file_id);

            if ($success) {
                $deleted_database++;
            }

            if ($action == 'delete_both' && $success && $this->deleteFromDisk($file)) {
                $deleted_disk++;
            }
        }

        $message = $this->text('Deleted from database: %db, disk: %disk', array(
            '%db' => $deleted_database, '%disk' => $deleted_disk));

        $this->session->setMessage($message, 'success');
        return true;
    }

    /**
     * Deletes a file from the disk
     * @param string $file
     */
    protected function deleteFromDisk($file)
    {
        return empty($file['path']) ? false : unlink(GC_FILE_DIR . '/' . $file['path']);
    }

    /**
     * Renders the file edit page
     */
    protected function outputEdit()
    {
        $this->output('content/file/edit');
    }

    /**
     * Sets titles on the file edit page
     * @param type $file
     */
    protected function setTitleEdit($file)
    {
        if (isset($file['file_id'])) {
            $title = $this->text('Edit file');
        } else {
            $title = $this->text('Add file');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the file edit page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Files'), 'url' => $this->url('admin/content/file')));
    }

    /**
     * Returns a file
     * @param integer $file_id
     * @return array
     */
    protected function get($file_id)
    {
        if (!is_numeric($file_id)) {
            return array();
        }

        $file = $this->file->get($file_id);

        if (empty($file)) {
            $this->outputError(404);
        }

        $file['file_url'] = '';
        if (file_exists(GC_FILE_DIR . '/' . $file['path'])) {
            $file['file_url'] = $this->file->url($file['path']);
        }

        return $file;
    }

    /**
     * Deletes a file
     * @param array $file
     */
    protected function delete(array $file)
    {
        $this->controlAccess('file_delete');

        if (!$this->file->delete($file['file_id'])) {
            $this->redirect('admin/content/file', $this->text('Unable to delete this file. The most probable reason - it is used somewhere'), 'danger');
        }

        if ($this->request->post('delete_disk') && $this->deleteFromDisk($file)) {
            $this->redirect('admin/content/file', $this->text('File has been deleted both from the database and disk'), 'success');
        }

        $this->redirect('admin/content/file', $this->text('File has been deleted from the database'), 'success');
    }

    /**
     * Saves a file
     * @param array $file
     * @return null
     */
    protected function submit(array $file)
    {
        $this->submitted = $this->request->post('file', array());

        $this->validate($file);

        $errors = $this->getErrors();

        if (!empty($errors)) {
            $this->data['file'] = $this->submitted + $file;
            return;
        }

        if (isset($file['file_id'])) {
            $this->controlAccess('file_edit');
            $this->file->update($file['file_id'], $this->submitted);

            if ($this->request->post('delete_disk') && $this->deleteFromDisk($file)) {
                $this->redirect('admin/content/file', $this->text('File has been updated, old file deleted from the disk'), 'success');
            }

            $this->redirect('admin/content/file', $this->text('File has been updated'), 'success');
        }

        $this->controlAccess('file_add');
        $this->file->add($this->submitted);
        $this->redirect('admin/content/file', $this->text('File has been added'), 'success');
    }

    /**
     * Validates an array of submitted data
     * @param array $file
     */
    protected function validate(array $file)
    {
        $this->validateFile($file);
        $this->validateTitle($file);
        $this->validateTranslation($file);
    }

    /**
     * Validates and uploads a file
     * @param array $file
     * @return boolean
     */
    protected function validateFile(array $file)
    {
        $upload = $this->request->file('file');

        if (!empty($upload)) {
            $result = $this->file->upload($upload);
            if ($result !== true) {
                $this->errors['file'] = $this->text('Unable to upload the file');
                return false;
            }

            $this->submitted['path'] = $this->file->path($this->file->getUploadedFile());
            return true;
        }

        if (empty($file['file_id']) || (isset($file['file_id']) && $this->request->post('delete_disk'))) {
            $this->errors['file'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates title field
     * @param array $file
     * @return boolean
     */
    protected function validateTitle(array $file)
    {
        if (empty($this->submitted['title']) || mb_strlen($this->submitted['title']) > 255) {
            $this->errors['title'] = $this->text('Content must be %min - %max characters long', array(
                '%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates file translations
     * @param array $file
     * @return boolean
     */
    protected function validateTranslation(array $file)
    {
        if (empty($this->submitted['translation'])) {
            return true;
        }

        $has_errors = false;
        foreach ($this->submitted['translation'] as $code => $translation) {
            if (mb_strlen($translation['title']) > 255) {
                $this->errors['translation'][$code]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

}
