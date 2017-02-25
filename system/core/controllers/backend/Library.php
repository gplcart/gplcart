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
        $this->clearCacheLibrary();

        $this->setTitleListLibrary();
        $this->setBreadcrumbListLibrary();

        $this->setData('libraries', $this->getListLibrary());

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
     * Clear library cache
     */
    protected function clearCacheLibrary()
    {
        if ($this->isQuery('refresh')) {
            $this->library->clearCache();
            $this->redirect('', $this->text('Cache has been cleared'), 'success');
        }
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
