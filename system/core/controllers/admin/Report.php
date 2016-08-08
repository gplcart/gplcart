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
        if ($this->request->get('clear_errors')) {
            $this->clearSystemErrors();
        }

        $errors = $this->report->getPhpErrors();

        if ($this->request->get('report') && !empty($errors)) {
            if ($this->report->reportErrors($errors)) {
                $this->redirect('', $this->text('Error raport has been sent'), 'success');
            }
            $this->redirect('', $this->text('Failed to send error report'), 'warning');
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalSystemEvents($query), $query);

        $filters = array('severity', 'type', 'time', 'text');
        $this->setFilter($filters, $query);

        $this->data['records'] = $this->getEvents($total, $query);
        $this->data['types'] = $this->report->getTypes();
        $this->data['can_report'] = (bool) $errors;

        $this->data['severities'] = array(
            'info' => $this->text('Info'),
            'warning' => $this->text('Warning'),
            'danger' => $this->text('Danger')
        );

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

        $store_id = (int) $this->request->get('store_id', $this->store->getDefault());
        $stores = $this->store->getList();
        $store = $this->store->get($store_id);

        $gapi_email = $this->config->get('gapi_email', '');
        $gapi_certificate = $this->config->get('gapi_certificate', '');

        $this->data['store'] = $store;
        $this->data['stores'] = $stores;
        $this->data['missing_settings'] = empty($store['data']['ga_view']);
        $this->data['missing_credentials'] = (empty($gapi_email) || empty($gapi_certificate));

        if ($this->data['missing_credentials'] || $this->data['missing_settings']) {
            $this->outputGa();
        }

        $this->setUpdateGa($store_id);

        $this->analytics->setCredentials($gapi_email, $gapi_certificate, "Analytics for {$store['domain']}");
        $this->analytics->setView($store['data']['ga_view']);
        $this->data['ga_view'] = $store['data']['ga_view'];

        $this->setPanelTraffic();
        $this->setPanelSoftware();
        $this->setPanelGaSources();
        $this->setPanelGaTopPages();
        $this->setPanelGaKeywords();

        $this->outputGa();
    }

    /**
     * Listen to URL parameter and updates cached GA data for the store ID
     * @param integer $store_id
     */
    protected function setUpdateGa($store_id)
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
    protected function setPanelGaKeywords()
    {
        $items = $this->analytics->get('keywords');
        $this->data['panel_keywords'] = $this->render('report/ga/panels/keywords', array('items' => $items));
    }

    /**
     * Sets Sources statistic panel
     */
    protected function setPanelGaSources()
    {
        $items = $this->analytics->get('sources');
        $this->data['panel_sources'] = $this->render('report/ga/panels/sources', array('items' => $items));
    }

    /**
     * Sets Top Pages statistic panel
     */
    protected function setPanelGaTopPages()
    {
        $items = $this->analytics->get('top_pages');

        foreach ($items as &$item) {
            if (preg_match('!^[\w.]*$!', $item[0])) {
                $item['url'] = $item[0] . $item[1];
            }
        }

        $this->data['panel_top_pages'] = $this->render('report/ga/panels/top_pages', array('items' => $items));
    }

    /**
     * Sets Software statistic panel
     */
    protected function setPanelSoftware()
    {

        $items = array();
        foreach ($this->analytics->get('software') as $i => $result) {
            $os_version = ($result[1] === "(not set)") ? '' : $result[1];
            $browser_version = ($result[3] === "(not set)") ? '' : $result[3];
            $items[$i][0] = $result[0] . " $os_version";
            $items[$i][1] = $result[2] . " $browser_version";
            $items[$i][2] = $result[4];
        }

        $this->data['panel_software'] = $this->render('report/ga/panels/software', array('items' => $items));
    }

    /**
     * Sets Traffic statistic panel
     */
    protected function setPanelTraffic()
    {
        $chart = $this->report->buildTrafficChart($this->analytics);

        $this->setJsSettings('chart_traffic', $chart);
        $this->setJs('files/assets/chart/Chart.min.js', 'top');

        $this->data['panel_traffic'] = $this->render('report/ga/panels/traffic');
    }

    /**
     * Displays the system status page
     */
    public function status()
    {
        $statuses = $this->report->getStatus();
        $this->data['statuses'] = $statuses;
        
        if($this->request->get('phpinfo')){
            $this->data['phpinfo'] = $this->report->phpinfo();
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
        return $this->report->getList(array('count' => true) + $query);
    }

    /**
     * Deletes all system events from the database
     */
    protected function clearSystemErrors()
    {
        $this->report->clear();
        $this->redirect('admin/report/system');
    }

    /**
     * Returns an array of system events
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getEvents(array $limit, array $query)
    {
        $records = $this->report->getList(array('limit' => $limit) + $query);

        foreach ($records as &$record) {
            $record['summary'] = '';
            $message_variables = isset($record['data']['variables']) ? $record['data']['variables'] : array();
            $record['text'] = $this->text($record['text'], $message_variables);
            $record['summary'] = $this->truncate($record['text']);
            $record['severity_text'] = $this->text($record['severity']);
            $record['time'] = $this->date($record['time']);
            $record['type'] = $this->text($record['type']);
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/report/ga'), 'text' => $this->text('Google Analytics')));
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/report/system'), 'text' => $this->text('System events')));
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
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the system status templates
     */
    protected function outputStatus()
    {
        $this->output('report/status');
    }

}
