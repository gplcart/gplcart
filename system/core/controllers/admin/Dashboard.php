<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Price as ModelsPrice;
use core\models\Order as ModelsOrder;
use core\models\Report as ModelsReport;
use core\models\Review as ModelsReview;
use core\models\Product as ModelsProduct;
use core\models\Analytics as ModelsAnalytics;

/**
 * Handles incoming requests and outputs data related to admin dashboard
 */
class Dashboard extends Controller
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Report model instance
     * @var \core\models\Report $report
     */
    protected $report;

    /**
     * Analytics model instance
     * @var \core\models\Analytics $analytics
     */
    protected $analytics;

    /**
     * Review model instance
     * @var \core\models\Review $review
     */
    protected $review;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsPrice $price
     * @param ModelsOrder $order
     * @param ModelsReport $report
     * @param ModelsAnalytics $analytics
     * @param ModelsReview $review
     */
    public function __construct(ModelsProduct $product, ModelsPrice $price,
            ModelsOrder $order, ModelsReport $report,
            ModelsAnalytics $analytics, ModelsReview $review)
    {
        parent::__construct();

        $this->price = $price;
        $this->order = $order;
        $this->report = $report;
        $this->review = $review;
        $this->product = $product;
        $this->analytics = $analytics;
    }

    /**
     * Displays the admin dashboard page
     */
    public function dashboard()
    {
        $this->data['stores'] = $this->store->getList();
        $this->data['store'] = $store = $this->getStore();
        $this->data['user_total'] = $this->user->getList(array('count' => true));
        $this->data['order_total'] = $this->order->getList(array('count' => true));
        $this->data['review_total'] = $this->review->getList(array('count' => true));
        $this->data['product_total'] = $this->product->getList(array('count' => true));

        $limit = $this->config->get('dashboard_limit', 10);

        $this->data['users'] = $this->getUsers($limit);
        $this->data['orders'] = $this->getOrders($limit);
        $this->data['system_events'] = $this->getEvents($limit);
        $this->data['severity_count'] = $this->getSeverityCount();

        $this->setGa($store);
        $this->setTitleDashboard();
        $this->outputDashboard();
    }

    /**
     * Renders the admin dashboard page
     */
    protected function outputDashboard()
    {
        $this->output('common/dashboard');
    }

    /**
     * Sets titles on the admin dashboard page
     */
    protected function setTitleDashboard()
    {
        $this->setTitle($this->text('Dashboard'), false);
    }

    /**
     * Returns a store from the current query
     * @return array
     */
    protected function getStore()
    {
        $store_id = $this->request->get('store_id');
        return $store_id ? $this->store->get($store_id) : $this->store->getDefault(true);
    }

    /**
     * Returns an array of orders
     * @param integer $limit
     * @return array
     */
    protected function getOrders($limit)
    {
        $orders = $this->order->getList(array('limit' => array(0, $limit)));

        array_walk($orders, function (&$order) {
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
            $order['html'] = $this->render('settings/search/suggestion/order', array('order' => $order));
        });

        return $orders;
    }

    /**
     * Returns an array of users
     * @param integer $limit
     * @return array
     */
    protected function getUsers($limit)
    {
        return $this->user->getList(array('limit' => array(0, $limit)));
    }

    /**
     * Returns an array of system event numbers keyed by severity type
     * @return array
     */
    protected function getSeverityCount()
    {
        $allowed = array_flip(array('danger', 'warning', 'info'));
        return array_filter(array_intersect_key($this->report->countSeverity(), $allowed));
    }

    /**
     * Returns an array of system events
     * @param integer $limit
     * @return array
     */
    protected function getEvents($limit)
    {
        $events = $this->report->getList(array(
            'limit' => array(0, $limit), 'severity' => $this->config->get('dashboard_severity', 'info')));

        foreach ($events as &$event) {
            $variables = empty($event['data']['variables']) ? array() : (array) $event['data']['variables'];
            $message = $this->text($event['text'], $variables);
            $event['message'] = $this->truncate($message);
        }

        return $events;
    }

    /**
     * Sets Google Analytics data
     * @param array $store
     * @return null
     */
    protected function setGa(array $store)
    {
        $gapi_email = $this->config->get('gapi_email', '');
        $gapi_certificate = $this->config->get('gapi_certificate', '');

        $this->data['chart_traffic'] = array();
        $this->data['ga_missing_settings'] = $this->data['gapi_missing_credentials'] = '';

        if (empty($gapi_email) || empty($gapi_certificate)) {
            $this->data['gapi_missing_credentials'] = $this->text('<a href="!href">Google API credentials</a> are not properly set up', array('!href' => $this->url('admin/settings/common')));
        }

        if (empty($store['data']['ga_view'])) {
            $this->data['ga_missing_settings'] = $this->text('<a href="!href">Google Analytics</a> is not properly set up', array('!href' => $this->url("admin/settings/store/{$store['store_id']}")));
        }

        if (!empty($this->data['gapi_missing_credentials']) || !empty($this->data['ga_missing_settings'])) {
            return;
        }

        $ga_view = $store['data']['ga_view'];

        if (!$this->access('report_ga')) {
            return;
        }

        if ($this->request->get('ga_update')) {
            $this->report->clearGaCache($ga_view);
            $this->url->redirect();
        }

        $this->analytics->setCredentials($gapi_email, $gapi_certificate, "Analytics for {$store['domain']}");
        $this->analytics->setView($ga_view);
        $this->data['chart_traffic'] = $this->report->buildTrafficChart($this->analytics);
        $this->addJsSettings('chart', array('traffic' => $this->data['chart_traffic']));
    }
}
