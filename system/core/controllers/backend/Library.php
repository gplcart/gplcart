<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to libraries
 */
class Library extends BackendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the zone overview page
     */
    public function listLibrary()
    {
        $this->library->clearCache();
        
        $this->getListLibrary();
        $this->getListLibrary();
        $this->getListLibrary();
        $this->getListLibrary();

        $libraries = $this->getListLibrary();
        $errors = $this->library->getErrors();

        $this->setData('errors', $errors);
        $this->setData('libraries', $libraries);

        $this->setTitleListLibrary();
        $this->setBreadcrumbListLibrary();
        
        $this->outputListLibrary();
    }

    /**
     * Returns an array of libraries
     * @return array
     */
    protected function getListLibrary()
    {
        $libraries = $this->library->getList();
        uasort($libraries, function($a, $b) {
            return strcmp($a['type'], $b['type']);
        });
        return $libraries;
    }

    /**
     * Sets titles on the libraries overview page
     */
    protected function setTitleListLibrary()
    {
        $this->setTitle($this->text('Libraries'));
    }

    /**
     * Sets breadcrumbs on the libraries overview page
     */
    protected function setBreadcrumbListLibrary()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the libraries overview page
     */
    protected function outputListLibrary()
    {
        $this->output('report/libraries');
    }

}
