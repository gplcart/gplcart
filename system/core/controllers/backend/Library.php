<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Library as LibraryModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to libraries
 */
class Library extends BackendController
{

    /**
     * Library model instance
     * @var \core\models\Library $library
     */
    protected $library;

    /**
     * Constructor
     * @param LibraryModel $library
     */
    public function __construct(LibraryModel $library)
    {
        parent::__construct();

        $this->library = $library;
    }

    /**
     * Returns the zone overview page
     */
    public function listLibrary()
    {
        $libraries = $this->getListLibrary();

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

        usort($libraries, function($a, $b){
            return strcmp($a["type"], $b["type"]);
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
