<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\traits\Listing as ListingTrait;

/**
 * Handles incoming requests and outputs data related to payment methods
 */
class ReportPayment extends Controller
{

    use ListingTrait;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * @param PaymentModel $payment
     */
    public function __construct(PaymentModel $payment)
    {
        parent::__construct();

        $this->payment = $payment;
    }

    /**
     * Displays the payment methods overview page
     */
    public function listReportPayment()
    {
        $this->setTitleListReportPayment();
        $this->setBreadcrumbListReportPayment();
        $this->setFilterListReportPayment();
        $this->setPagerListReportPayment();

        $this->setData('methods', (array) $this->getListReportPayment());
        $this->outputListReportPayment();
    }

    /**
     * Sets the filter on the payment methods overview page
     */
    protected function setFilterListReportPayment()
    {
        $this->setFilter($this->getAllowedFiltersReportPayment());
    }

    /**
     * Returns an array of allowed fields for sorting and filtering
     * @return array
     */
    protected function getAllowedFiltersReportPayment()
    {
        return array('id', 'title', 'status', 'module');
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReportPayment()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListReportPayment(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of payment methods
     * @param bool $count
     * @return array|int
     */
    protected function getListReportPayment($count = false)
    {
        $list = $this->payment->getList();

        $allowed = $this->getAllowedFiltersReportPayment();
        $this->filterList($list, $allowed, $this->query_filter);
        $this->sortList($list, $allowed, $this->query_filter, array('id' => 'asc'));

        if ($count) {
            return count($list);
        }

        $this->limitList($list, $this->data_limit);
        return $list;
    }

    /**
     * Sets title on the payment method overview page
     */
    protected function setTitleListReportPayment()
    {
        $this->setTitle($this->text('Payment methods'));
    }

    /**
     * Sets breadcrumbs on the payment method overview page
     */
    protected function setBreadcrumbListReportPayment()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates on the payment method overview page
     */
    protected function outputListReportPayment()
    {
        $this->output('report/payment_methods');
    }

}
