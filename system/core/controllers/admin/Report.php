<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Report as R;
use core\models\Analytics;
use core\models\Notification;

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
     * Notification model instance
     * @var \core\models\Notification $notification
     */
    protected $notification;

    /**
     * Constructor
     * @param R $report
     * @param Analytics $analytics
     */
    public function __construct(R $report, Analytics $analytics, Notification $notification)
    {
        parent::__construct();

        $this->report = $report;
        $this->analytics = $analytics;
        $this->notification = $notification;
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

        if ($this->request->get('report') && $errors) {
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

        $this->data['severity_count'] = $this->getSeverityCount();

        $this->setTitleSystem();
        $this->setBreadcrumbSystem();
        $this->outputSystem();
    }

    /**
     * Returns a number of total system events for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalSystemEvents($query)
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
    protected function getEvents($limit, $query)
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
     * Returns an array of counted by severity system events
     * @return type
     */
    protected function getSeverityCount()
    {
        $allowed = array_flip(array('danger', 'warning', 'info'));
        return array_filter(array_intersect_key($this->report->countSeverity(), $allowed));
    }

    /**
     * Sets titles on the system notifications overview page
     */
    protected function setTitleSystem()
    {
        $this->setTitle($this->text('System events'));
    }

    /**
     * Sets breadcrumbs on the system notifications overview page
     */
    protected function setBreadcrumbSystem()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/report/ga'), 'text' => $this->text('Google Analytics')));
    }

    /**
     * Renders the system notifications overview page
     */
    protected function outputSystem()
    {
        $this->output('report/system');
    }

    /**
     * Displays Google Analytics page
     */
    public function ga()
    {
        $this->setTitleGa();
        $this->setBreadcrumbGa();

        $gapi_email = $this->config->get('gapi_email', '');
        $gapi_certificate = $this->config->get('gapi_certificate', '');

        if (!$gapi_email || !$gapi_certificate) {
            $this->data['missing_credentials'] = $this->text('<a href="!href">Google API credentials</a> are not properly set', array('!href' => $this->url('admin/settings/common')));
            $this->outputGa();
        }

        $default_store = $this->store->getDefault();
        $store_id = $this->request->get('store_id', $default_store);

        $stores = $this->store->getList();
        $store = isset($stores[$store_id]) ? $stores[$store_id] : $stores[$default_store];

        $this->data['stores'] = $stores;
        $this->data['store'] = $store;
        $this->data['traffic'] = array();
        $this->data['software'] = array();

        if (empty($store['data']['ga_view'])) {
            $this->data['missing_settings'] = $this->text('<a href="!href">Google Analytics</a> is not properly set', array('!href' => $this->url("admin/settings/store/$store_id")));
            $this->outputGa();
        }

        $view = $this->request->get('ga_view');

        if ($this->request->get('ga_update') && $view) {
            $this->report->clearGaCache($view);
            $this->session->setMessage($this->text('Google Analytics has been updated'), 'success');
            $this->url->redirect('admin/report/ga', array('store_id' => $store_id));
        }

        $this->setGa($store, $gapi_email, $gapi_certificate);
        $this->outputGa();
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
        $this->output('report/ga');
    }

    /**
     * Sets Google analytics data on the page
     * @param array $store
     * @param string $gapi_email
     * @param string $gapi_certificate
     */
    protected function setGa($store, $gapi_email, $gapi_certificate)
    {
        $ga_view = $store['data']['ga_view'];

        $this->analytics->setCredentials($gapi_email, $gapi_certificate, "Analytics for {$store['domain']}");
        $this->analytics->setView($ga_view);


        $this->data['keywords'] = $this->analytics->getKeywords();
        $this->data['sources'] = $this->analytics->getSources();
        $this->data['top_pages'] = $this->analytics->getTopPages();
        $this->data['software'] = $this->getGaSoftware();
        $this->data['ga_view'] = $ga_view;
        $this->data['chart_traffic'] = $this->report->buildTrafficChart($this->analytics);

        $this->addJsSettings('chart', array('traffic' => $this->data['chart_traffic']));
    }

    /**
     * Returns an array of software data from GA
     * @return array
     */
    protected function getGaSoftware()
    {
        $results = array();
        foreach ($this->analytics->getSoftware() as $i => $result) {
            $os_version = ($result[1] === "(not set)") ? '' : $result[1];
            $browser_version = ($result[3] === "(not set)") ? '' : $result[3];
            $results[$i][0] = $result[0] . " $os_version";
            $results[$i][1] = $result[2] . " $browser_version";
            $results[$i][2] = $result[4];
        }

        return $results;
    }

    /**
     * Displays the notification overview admin page
     */
    public function notifications()
    {
        $this->data['notifications_list'] = $this->getNotifications();

        if ($this->request->get('clear')) {
            $this->clearNotification();
            $this->redirect();
        }

        $this->setTitleNotifications();
        $this->setBreadcrumbNotifications();
        $this->outputNotifications();
    }

    /**
     * Removes a message from saved notifications
     * @return boolean
     */
    protected function clearNotification()
    {
        $index = $this->request->get('index');
        $notification_id = $this->request->get('notification_id');
        $notification = $this->notification->get($notification_id);

        if (empty($notification['messages'])) {
            return false;
        }

        unset($notification['messages'][$index]);

        if (empty($notification['messages'])) {
            return $this->notification->clear($notification_id);
        }

        return $this->notification->save($notification_id, $notification);
    }

    /**
     * Sets titles on the notification overview admin page
     */
    protected function setTitleNotifications()
    {
        $this->setTitle('Notifications');
    }

    /**
     * Sets breadcrumbs on the notification overview admin page
     */
    protected function setBreadcrumbNotifications()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the notification page templates
     */
    protected function outputNotifications()
    {
        $this->output('report/notifications');
    }

    /**
     * Returns an array of notifications
     * @return array
     */
    protected function getNotifications()
    {
        $notifications = array();

        foreach ($this->notification->getList() as $notification_id => $notification) {
            if (empty($notification['messages'])) {
                continue;
            }

            if ($notification['access'] && !$this->access($notification['access'])) {
                continue;
            }

            foreach ($notification['messages'] as $index => $message) {
                if (empty($message['message'])) {
                    continue;
                }

                if (!isset($message['variables'])) {
                    $message['variables'] = array();
                }

                $notifications[$notification_id][$index] = array(
                    'message' => $this->text($message['message'], $message['variables']),
                    'severity' => isset($message['severity']) ? $message['severity'] : 'info'
                );
            }
        }

        return $notifications;
    }
}
