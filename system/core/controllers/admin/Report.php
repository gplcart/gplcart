<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Report as ModelsReport;
use core\models\Analytics as ModelsAnalytics;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to system reports
 */
class Report extends BackendController
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
     * Displays Google Analytics page
     */
    public function listGaReport()
    {
        $this->setTitleListGaReport();
        $this->setBreadcrumbListGaReport();

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
            $this->outputListGaReport();
        }

        $this->updateGaReport($store_id);

        $view = $store['data']['ga_view'];
        $this->analytics->setCredentials($email, $certificate, "Analytics for {$store['domain']}");
        $this->analytics->setView($view);

        $this->setData('ga_view', $view);

        $this->setDataGaTrafficReport();
        $this->setDataGaSoftwareReport();
        $this->setDataGaSourcesReport();
        $this->setDataGaTopPagesReport();
        $this->setDataGaKeywordsReport();

        $this->outputListGaReport();
    }

    /**
     * Listen to URL parameter and updates cached GA data for the store ID
     * @param integer $store_id
     */
    protected function updateGaReport($store_id)
    {
        $view = (string) $this->request->get('ga_view');

        if ($this->isQuery('ga_update') && !empty($view)) {
            $this->report->clearGaCache($view);
            $this->setMessage($this->text('Google Analytics has been updated'), 'success', true);
            $this->url->redirect('admin/report/ga', array('store_id' => $store_id));
        }
    }

    /**
     * Sets Keywords statistic panel
     */
    protected function setDataGaKeywordsReport()
    {
        $items = $this->analytics->get('keywords');
        $html = $this->render('report/ga/panels/keywords', array('items' => $items));

        $this->setData('panel_keywords', $html);
    }

    /**
     * Sets Sources statistic panel
     */
    protected function setDataGaSourcesReport()
    {
        $items = $this->analytics->get('sources');
        $html = $this->render('report/ga/panels/sources', array('items' => $items));

        $this->setData('panel_sources', $html);
    }

    /**
     * Sets Top Pages statistic panel
     */
    protected function setDataGaTopPagesReport()
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
    protected function setDataGaSoftwareReport()
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
    protected function setDataGaTrafficReport()
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
            $phpinfo = $this->report->phpinfo();
            $this->setData('phpinfo', $phpinfo);
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
        return $this->report->getList($query);
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
     * Returns an array of system events
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListEventReport(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $records = $this->report->getList($query);

        foreach ($records as &$record) {

            $variables = array();
            if (!empty($record['data']['variables'])) {
                $variables = $record['data']['variables'];
            }

            $record['time'] = $this->date($record['time']);
            $record['type'] = $this->text("event_{$record['type']}");
            
            if(!empty($record['translatable'])){
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
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/report/ga'),
            'text' => $this->text('Google Analytics'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the system events overview page
     */
    protected function outputListEventReport()
    {
        $this->output('report/events');
    }

    /**
     * Sets titles on the GA page
     */
    protected function setTitleListGaReport()
    {
        $this->setTitle($this->text('Google Analytics'));
    }

    /**
     * Sets breadcrumbs on the GA page
     */
    protected function setBreadcrumbListGaReport()
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/report/events'),
            'text' => $this->text('System events'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the GA page templates
     */
    protected function outputListGaReport()
    {
        $this->output('report/ga/ga');
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
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the system status templates
     */
    protected function outputListStatusReport()
    {
        $this->output('report/status');
    }

}
