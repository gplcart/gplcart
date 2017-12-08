<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to libraries
 */
class ReportLibrary extends BackendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the library overview page
     */
    public function listReportLibrary()
    {
        $this->clearCacheReportLibrary();

        $this->setTitleListReportLibrary();
        $this->setBreadcrumbListReportLibrary();

        $this->setData('libraries', $this->getListReportLibrary());
        $this->outputListReportLibrary();
    }

    /**
     * Returns an array of libraries
     * @return array
     */
    protected function getListReportLibrary()
    {
        $libraries = $this->library->getList();

        uasort($libraries, function($a, $b) {
            return strcmp($a['type'], $b['type']);
        });

        return $libraries;
    }

    /**
     * Clear cached libraries
     */
    protected function clearCacheReportLibrary()
    {
        $this->controlToken('refresh');

        if ($this->isQuery('refresh') && $this->library->clearCache()) {
            $this->redirect('', $this->text('Cache has been deleted'), 'success');
        }
    }

    /**
     * Sets titles on the library overview page
     */
    protected function setTitleListReportLibrary()
    {
        $this->setTitle($this->text('Libraries'));
    }

    /**
     * Sets breadcrumbs on the library overview page
     */
    protected function setBreadcrumbListReportLibrary()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the library overview page
     */
    protected function outputListReportLibrary()
    {
        $this->output('report/libraries');
    }

}
