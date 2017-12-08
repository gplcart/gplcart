<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to shipping methods
 */
class ReportShipping extends BackendController
{

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

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

        $this->setData('methods', $this->getListReportShipping());

        $this->outputListReportShipping();
    }

    /**
     * Returns an array of shipping methods
     * @return array
     */
    protected function getListReportShipping()
    {
        return $this->shipping->getList();
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
