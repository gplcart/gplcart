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
    public function listFile()
    {
        if ($this->isQuery('download')) {
            $this->downloadFile();
        }

        if ($this->isPosted('action')) {
            $this->actionFile();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalFile($query);
        $limit = $this->setPager($total, $query);
        $files = $this->getListFile($limit, $query);

        $this->setData('files', $files);

        $allowed = array('title', 'mime_type', 'created', 'path');
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
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
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
        $selected = (array) $this->request->post('selected', array());

        $deleted_disk = $deleted_database = 0;

        foreach ($selected as $file_id) {
            if ($action === 'delete' && $this->access('file_delete')) {
                $file = $this->file->get($file_id);
                $deleted_database += (int) $this->file->delete($file_id);
                $deleted_disk += (int) $this->file->deleteFromDisk($file);
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
        $file_id = (int) $this->request->get('download');
        $file = $this->file->get($file_id);

        $filepath = GC_FILE_DIR . '/' . $file['path'];
        $this->response->download($filepath);
    }

}
