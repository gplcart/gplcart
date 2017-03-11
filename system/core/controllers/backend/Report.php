<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Report as ReportModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Shipping as ShippingModel,
    gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to various reports
 */
class Report extends BackendController
{

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Constructor
     * @param ReportModel $report
     * @param UserRoleModel $role
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     */
    public function __construct(ReportModel $report, UserRoleModel $role,
            PaymentModel $payment, ShippingModel $shipping)
    {
        parent::__construct();

        $this->role = $role;
        $this->report = $report;
        $this->payment = $payment;
        $this->shipping = $shipping;
    }

    /**
     * Displays the payment methods overview page
     */
    public function listPaymentMethodsReport()
    {
        $this->setTitleListPaymentMethodsReport();
        $this->setBreadcrumbListPaymentMethodsReport();

        $this->setData('methods', $this->payment->getList());
        $this->outputListPaymentMethodsReport();
    }

    /**
     * Sets title on the payment methods overview page
     */
    protected function setTitleListPaymentMethodsReport()
    {
        $this->setTitle($this->text('Payment methods'));
    }

    /**
     * Sets breadcrumbs on the payment methods overview page
     */
    protected function setBreadcrumbListPaymentMethodsReport()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates on the payment methods overview page
     */
    protected function outputListPaymentMethodsReport()
    {
        $this->output('report/payment_methods');
    }

    /**
     * Displays the shipping methods overview page
     */
    public function listShippingMethodsReport()
    {
        $this->setTitleListShippingMethodsReport();
        $this->setBreadcrumbListShippingMethodsReport();

        $this->setData('methods', $this->shipping->getList());

        $this->outputListShippingMethodsReport();
    }

    /**
     * Sets title on the shipping methods overview page
     */
    protected function setTitleListShippingMethodsReport()
    {
        $this->setTitle($this->text('Shipping methods'));
    }

    /**
     * Sets breadcrumbs on the shipping methods overview page
     */
    protected function setBreadcrumbListShippingMethodsReport()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates on the shipping methods overview page
     */
    protected function outputListShippingMethodsReport()
    {
        $this->output('report/shipping_methods');
    }

    /**
     * Displays the routes overview page
     */
    public function listRoutesReport()
    {
        $this->setTitleListRoutesReport();
        $this->setBreadcrumbListRoutesReport();

        $this->setData('routes', $this->getRoutesReport());
        $this->outputListRoutesReport();
    }

    /**
     * Returns an array of prepared routes 
     */
    protected function getRoutesReport()
    {
        $routes = $this->route->getList();
        return $this->prepareRoutesReport($routes);
    }

    /**
     * Prepares an array of routes
     * @param array $routes
     * @return array
     */
    protected function prepareRoutesReport(array $routes)
    {
        $permissions = $this->role->getPermissions();

        foreach ($routes as $pattern => &$route) {

            if (strpos($pattern, 'admin') === 0) {
                $route['permission_name'] = array($this->text($permissions['admin']));
            } else {
                $route['permission_name'] = array($this->text('Public'));
            }

            if (!isset($route['access'])) {
                continue;
            }

            if (!isset($permissions[$route['access']])) {
                $route['permission_name'] = array($this->text('Unknown'));
                continue;
            }

            $route['permission_name'][] = $this->text($permissions[$route['access']]);
        }

        ksort($routes);
        return $routes;
    }

    /**
     * Sets title on the routes overview page
     */
    protected function setTitleListRoutesReport()
    {
        $this->setTitle($this->text('Routes'));
    }

    /**
     * Sets breadcrumbs on the routes overview page
     */
    protected function setBreadcrumbListRoutesReport()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates on the routes overview page
     */
    protected function outputListRoutesReport()
    {
        $this->output('report/routes');
    }

    /**
     * Displays the system events overview page
     */
    public function listEventReport()
    {
        $this->clearEventReport();

        $this->setTitleListEventReport();
        $this->setBreadcrumbListEventReport();

        $query = $this->getFilterQuery();

        $filters = array('severity', 'type', 'time', 'text');
        $this->setFilter($filters, $query);

        $total = $this->getTotalEventReport($query);
        $limit = $this->setPager($total, $query);

        $this->setData('types', $this->report->getTypes());
        $this->setData('severities', $this->report->getSeverities());
        $this->setData('records', $this->getListEventReport($limit, $query));

        $this->outputListEventReport();
    }

    /**
     * Deletes all system events from the database
     */
    protected function clearEventReport()
    {
        if ($this->isQuery('clear')) {
            $this->report->clear();
            $this->redirect('admin/report/events');
        }
    }

    /**
     * Returns a number of total system events for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalEventReport(array $query)
    {
        $query['count'] = true;
        return (int) $this->report->getList($query);
    }

    /**
     * Returns an array of system events
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListEventReport(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $records = (array) $this->report->getList($query);

        return $this->prepareListEventReport($records);
    }

    /**
     * Adds an additional data to the event recors
     * @param array $records
     * @return array
     */
    protected function prepareListEventReport(array $records)
    {
        foreach ($records as &$record) {

            $variables = array();
            if (!empty($record['data']['variables'])) {
                $variables = $record['data']['variables'];
            }

            $record['time'] = $this->date($record['time']);

            $type = "event_{$record['type']}";
            $record['type'] = $this->text($type);

            if (!empty($record['translatable'])) {
                $record['text'] = $this->text($record['text'], $variables);
            }

            $record['summary'] = $this->truncate($record['text']);
            $record['severity_text'] = $this->text($record['severity']);
        }

        return $records;
    }

    /**
     * Sets titles on the system events overview page
     */
    protected function setTitleListEventReport()
    {
        $this->setTitle($this->text('System events'));
    }

    /**
     * Sets breadcrumbs on the system events overview page
     */
    protected function setBreadcrumbListEventReport()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the system events overview page
     */
    protected function outputListEventReport()
    {
        $this->output('report/events');
    }

    /**
     * Displays the system status page
     */
    public function listStatusReport()
    {
        $this->setTitleListStatusReport();
        $this->setBreadcrumbListStatusReport();

        $this->setData('statuses', $this->report->getStatus());

        $this->setDataStatusReport();
        $this->outputListStatusReport();
    }

    /**
     * Sets an additional data on the status report page
     */
    protected function setDataStatusReport()
    {
        if ($this->isQuery('phpinfo')) {
            $this->setData('phpinfo', gplcart_phpinfo());
        }
    }

    /**
     * Sets titles on the system status page
     */
    protected function setTitleListStatusReport()
    {
        $this->setTitle('System status');
    }

    /**
     * Sets breadcrumbs on the system status page
     */
    protected function setBreadcrumbListStatusReport()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the system status templates
     */
    protected function outputListStatusReport()
    {
        $this->output('report/status');
    }

}
