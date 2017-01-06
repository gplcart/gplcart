<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Report as ReportModel;
use gplcart\core\models\UserRole as UserRoleModel;
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
     * Constructor
     * @param ReportModel $report
     * @param UserRoleModel $role
     */
    public function __construct(ReportModel $report, UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
        $this->report = $report;
    }

    /**
     * Displays the routes overview page
     */
    public function listRoutesReport()
    {
        $routes = $this->getRoutesReport();
        $this->setData('routes', $routes);

        $this->setTitleListRoutesReport();
        $this->setBreadcrumbListRoutesReport();
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
                $route['permission_name'] = array($permissions['admin']);
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

            $route['permission_name'][] = $permissions[$route['access']];
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

        $query = $this->getFilterQuery();
        $total = $this->getTotalEventReport($query);
        $limit = $this->setPager($total, $query);

        $filters = array('severity', 'type', 'time', 'text');
        $this->setFilter($filters, $query);

        $types = $this->report->getTypes();
        $events = $this->getListEventReport($limit, $query);
        $severities = $this->report->getSeverities();

        $this->setData('types', $types);
        $this->setData('records', $events);
        $this->setData('severities', $severities);

        $this->setTitleListEventReport();
        $this->setBreadcrumbListEventReport();
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
            $record['type'] = $this->text("event_{$record['type']}");

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
        $statuses = $this->report->getStatus();
        $this->setData('statuses', $statuses);

        $this->setDataStatusReport();

        $this->setTitleListStatusReport();
        $this->setBreadcrumbListStatusReport();
        $this->outputListStatusReport();
    }

    /**
     * Sets an additional data on the status report page
     */
    protected function setDataStatusReport()
    {
        if ($this->isQuery('phpinfo')) {
            $phpinfo = gplcart_phpinfo();
            $this->setData('phpinfo', $phpinfo);
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
