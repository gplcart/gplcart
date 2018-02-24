<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\traits\Listing as ListingTrait;

/**
 * Handles incoming requests and outputs data related to libraries
 */
class ReportLibrary extends Controller
{

    use ListingTrait;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * Displays the library overview page
     */
    public function listReportLibrary()
    {
        $this->clearCacheReportLibrary();
        $this->setTitleListReportLibrary();
        $this->setBreadcrumbListReportLibrary();
        $this->setFilterListReportLibrary();
        $this->setPagerListReportLibrary();

        $this->setData('types', $this->getTypesLibrary());
        $this->setData('libraries', (array) $this->getListReportLibrary());

        $this->outputListReportLibrary();
    }

    /**
     * Sets the filter on the library overview page
     */
    protected function setFilterListReportLibrary()
    {
        $this->setFilter($this->getAllowedFiltersReportLibrary());
    }

    /**
     * Returns an array of allowed fields for sorting and filtering
     * @return array
     */
    protected function getAllowedFiltersReportLibrary()
    {
        return array('name', 'id', 'type', 'has_dependencies', 'status', 'version');
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReportLibrary()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListReportLibrary(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of libraries
     * @param bool $count
     * @return array|int
     */
    protected function getListReportLibrary($count = false)
    {
        $list = $this->library->getList();
        $this->prepareListLibrary($list);

        $allowed = $this->getAllowedFiltersReportLibrary();

        $this->filterList($list, $allowed, $this->query_filter);
        $this->sortList($list, $allowed, $this->query_filter, array('name' => 'desc'));

        if ($count) {
            return count($list);
        }

        $this->limitList($list, $this->data_limit);
        return $list;
    }

    /**
     * Prepare an array of libraries
     * @param array $list
     */
    protected function prepareListLibrary(array &$list)
    {
        foreach ($list as &$item) {
            $item['status'] = empty($item['errors']);
            $item['has_dependencies'] = !empty($item['requires']) || !empty($item['required_by']);
        }
    }

    /**
     * Returns an array of library type names
     * @return array
     */
    protected function getTypesLibrary()
    {
        $types = array();

        foreach (array_keys($this->library->getTypes()) as $id) {
            $types[$id] = $this->text(ucfirst($id));
        }

        return $types;
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
