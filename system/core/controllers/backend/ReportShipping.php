<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\traits\Listing as ListingTrait;

/**
 * Handles incoming requests and outputs data related to shipping methods
 */
class ReportShipping extends BackendController
{

    use ListingTrait;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * @param ShippingModel $shipping
     */
    public function __construct(ShippingModel $shipping)
    {
        parent::__construct();

        $this->shipping = $shipping;
    }

    /**
     * Displays the shipping method overview page
     */
    public function listReportShipping()
    {
        $this->setTitleListReportShipping();
        $this->setBreadcrumbListReportShipping();
        $this->setFilterListReportShipping();
        $this->setPagerListReportShipping();

        $this->setData('methods', (array) $this->getListReportShipping());

        $this->outputListReportShipping();
    }

    /**
     * Sets the filter on the shipping methods overview page
     */
    protected function setFilterListReportShipping()
    {
        $this->setFilter($this->getAllowedFiltersReportShipping());
    }

    /**
     * Returns an array of allowed fields for sorting and filtering
     * @return array
     */
    protected function getAllowedFiltersReportShipping()
    {
        return array('id', 'title', 'status', 'module');
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReportShipping()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListReportShipping(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of shipping methods or counts them
     * @param bool $count
     * @return array|int
     */
    protected function getListReportShipping($count = false)
    {
        $list = $this->shipping->getList();
        $allowed = $this->getAllowedFiltersReportShipping();

        $this->filterList($list, $allowed, $this->query_filter);
        $this->sortList($list, $allowed, $this->query_filter, array('id' => 'asc'));

        if ($count) {
            return count($list);
        }

        $this->limitList($list, $this->data_limit);
        return $list;
    }

    /**
     * Sets title on the shipping method overview page
     */
    protected function setTitleListReportShipping()
    {
        $this->setTitle($this->text('Shipping methods'));
    }

    /**
     * Sets breadcrumbs on the shipping method overview page
     */
    protected function setBreadcrumbListReportShipping()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the shipping method overview page
     */
    protected function outputListReportShipping()
    {
        $this->output('report/shipping_methods');
    }

}
