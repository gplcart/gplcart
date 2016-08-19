<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Report as ModelsReport;
use core\models\Analytics as ModelsAnalytics;

/**
 * Handles incoming requests and outputs data related to system reports
 */
class Report extends Controller
{

    /**
     * Report model instance
     * @var \core\models\Report $report
     */
    protected $report;

    /**
     * Analytics model instance
     * @var \core\models\Analytics $ga
     */
    protected $ga;

    /**
     * Constructor
     * @param ModelsReport $report
     * @param ModelsAnalytics $analytics
     */
    public function __construct(ModelsReport $report, ModelsAnalytics $analytics)
    {
        parent::__construct();

        $this->report = $report;
        $this->analytics = $analytics;
    }

    /**
     * Displays the system events overview page
     */
    public function system()
    {
        $this->clearSystemErrors();

        $query = $this->getFilterQuery();
        $total = $this->getTotalSystemEvents($query);
        $limit = $this->setPager($total, $query);

        $filters = array('severity', 'type', 'time', 'text');
        $this->setFilter($filters, $query);

        $types = $this->report->getTypes();
        $events = $this->getEvents($limit, $query);
        $severities = $this->report->getSeverities();

        $this->setData('types', $types);
        $this->setData('records', $events);
        $this->setData('severities', $severities);

        $this->setTitleSystem();
        $this->setBreadcrumbSystem();
        $this->outputSystem();
    }

    /**
     * Displays Google Analytics page
     */
    public function ga()
    {
        $this->setTitleGa();
        $this->setBreadcrumbGa();

        $default_store = $this->store->getDefault();
        $store_id = (int) $this->request->get('store_id', $default_store);
        $stores = $this->store->getList();
        $store = $this->store->get($store_id);

        $email = $this->config('gapi_email', '');
        $certificate = $this->config('gapi_certificate', '');
        $missing_settings = empty($store['data']['ga_view']);
        $missing_credentials = (empty($email) || empty($certificate));

        $this->setData('store', $store);
        $this->setData('stores', $stores);
        $this->setData('missing_settings', $missing_settings);
        $this->setData('missing_credentials', $missing_credentials);

        if ($missing_settings || $missing_credentials) {
            $this->outputGa();
        }

        $this->updateGa($store_id);

        $view = $store['data']['ga_view'];
        $this->analytics->setCredentials($email, $certificate, "Analytics for {$store['domain']}");
        $this->analytics->setView($view);

        $this->setData('ga_view', $view);

        $this->setDataPanelTraffic();
        $this->setDataPanelSoftware();
        $this->setDataPanelSources();
        $this->setDataPanelTopPages();
        $this->setDataPanelKeywords();

        $this->outputGa();
    }

    /**
     * Listen to URL parameter and updates cached GA data for the store ID
     * @param integer $store_id
     */
    protected function updateGa($store_id)
    {
        $view = (string) $this->request->get('ga_view');

        if ($this->request->get('ga_update') && !empty($view)) {
            $this->report->clearGaCache($view);
            $this->session->setMessage($this->text('Google Analytics has been updated'), 'success');
            $this->url->redirect('admin/report/ga', array('store_id' => $store_id));
        }
    }

    /**
     * Sets Keywords statistic panel
     */
    protected function setDataPanelKeywords()
    {
        $items = $this->analytics->get('keywords');
        $html = $this->render('report/ga/panels/keywords', array('items' => $items));

        $this->setData('panel_keywords', $html);
    }

    /**
     * Sets Sources statistic panel
     */
    protected function setDataPanelSources()
    {
        $items = $this->analytics->get('sources');
        $html = $this->render('report/ga/panels/sources', array('items' => $items));

        $this->setData('panel_sources', $html);
    }

    /**
     * Sets Top Pages statistic panel
     */
    protected function setDataPanelTopPages()
    {
        $items = $this->analytics->get('top_pages');

        foreach ($items as &$item) {
            if (preg_match('!^[\w.]*$!', $item[0])) {
                $item['url'] = $item[0] . $item[1];
            }
        }

        $html = $this->render('report/ga/panels/top_pages', array('items' => $items));
        $this->setData('panel_top_pages', $html);
    }

    /**
     * Sets Software statistic panel
     */
    protected function setDataPanelSoftware()
    {
        $items = array();
        foreach ($this->analytics->get('software') as $i => $result) {

            $os_version = ($result[1] === "(not set)") ? '' : $result[1];
            $browser_version = ($result[3] === "(not set)") ? '' : $result[3];

            $items[$i][0] = $result[0] . " $os_version";
            $items[$i][1] = $result[2] . " $browser_version";
            $items[$i][2] = $result[4];
        }

        $html = $this->render('report/ga/panels/software', array('items' => $items));
        $this->setData('panel_software', $html);
    }

    /**
     * Sets Traffic statistic panel
     */
    protected function setDataPanelTraffic()
    {
        $chart = $this->report->buildTrafficChart($this->analytics);

        $this->setJsSettings('chart_traffic', $chart);
        $this->setJs('files/assets/chart/Chart.min.js', 'top');

        $html = $this->render('report/ga/panels/traffic');
        $this->setData('panel_traffic', $html);
    }

    /**
     * Displays the system status page
     */
    public function status()
    {
        $statuses = $this->report->getStatus();
        $this->setData('statuses', $statuses);

        if ($this->request->get('phpinfo')) {
            $phpinfo = $this->report->phpinfo();
            $this->setData('phpinfo', $phpinfo);
        }

        $this->setTitleStatus();
        $this->setBreadcrumbStatus();
        $this->outputStatus();
    }

    /**
     * Returns a number of total system events for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalSystemEvents(array $query)
    {
        $query['count'] = true;
        return $this->report->getList($query);
    }

    /**
     * Deletes all system events from the database
     */
    protected function clearSystemErrors()
    {
        if ($this->request->get('clear_errors')) {
            $this->report->clear();
            $this->redirect('admin/report/system');
        }
    }

    /**
     * Returns an array of system events
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getEvents(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $records = $this->report->getList($query);

        foreach ($records as &$record) {

            $variables = array();
            if (!empty($record['data']['variables'])) {
                $variables = $record['data']['variables'];
            }

            $record['time'] = $this->date($record['time']);
            $record['type'] = $this->text($record['type']);
            $record['text'] = $this->text($record['text'], $variables);
            $record['summary'] = $this->truncate($record['text']);
            $record['severity_text'] = $this->text($record['severity']);
        }

        return $records;
    }

    /**
     * Sets titles on the system events overview page
     */
    protected function setTitleSystem()
    {
        $this->setTitle($this->text('System events'));
    }

    /**
     * Sets breadcrumbs on the system events overview page
     */
    protected function setBreadcrumbSystem()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/report/ga'),
            'text' => $this->text('Google Analytics')));
    }

    /**
     * Renders the system events overview page
     */
    protected function outputSystem()
    {
        $this->output('report/system');
    }

    /**
     * Sets titles on the GA page
     */
    protected function setTitleGa()
    {
        $this->setTitle($this->text('Google Analytics'));
    }

    /**
     * Sets breadcrumbs on the GA page
     */
    protected function setBreadcrumbGa()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/report/system'),
            'text' => $this->text('System events')));
    }

    /**
     * Renders the GA page templates
     */
    protected function outputGa()
    {
        $this->output('report/ga/ga');
    }

    /**
     * Sets titles on the system status page
     */
    protected function setTitleStatus()
    {
        $this->setTitle('System status');
    }

    /**
     * Sets breadcrumbs on the system status page
     */
    protected function setBreadcrumbStatus()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders the system status templates
     */
    protected function outputStatus()
    {
        $this->output('report/status');
    }

}
