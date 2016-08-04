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
        $this->setDownload();

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

            if ($action !== 'delete') {
                continue;
            }

            if (!$this->access('file_delete')) {
                continue;
            }

            $file = $this->file->get($file_id);

            if (empty($file)) {
                continue;
            }

            $deleted_database += (int) $this->file->delete($file_id);
            $deleted_disk += (int) $this->file->deleteFromDisk($file);
        }

        $message = $this->text('Deleted from database: %db, disk: %disk', array(
            '%db' => $deleted_database, '%disk' => $deleted_disk));

        $this->session->setMessage($message, 'success');
        return true;
    }

    /**
     * Downloads a file using a file id from the URL
     */
    protected function setDownload()
    {
        $file_id = (int) $this->request->get('download');

        if (!empty($file_id)) {
            $file = $this->file->get($file_id);

            if (!empty($file['path'])) {
                $this->response->download(GC_FILE_DIR . '/' . $file['path']);
            }
        }
    }

}
