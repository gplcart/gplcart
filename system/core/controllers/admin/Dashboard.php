<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\controllers\admin\Controller as BackendController;
use core\models\Analytics as ModelsAnalytics;
use core\models\Order as ModelsOrder;
use core\models\Price as ModelsPrice;
use core\models\Product as ModelsProduct;
use core\models\Report as ModelsReport;
use core\models\Review as ModelsReview;

/**
 * Handles incoming requests and outputs data related to admin dashboard
 */
class Dashboard extends BackendController
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
     * Max items to be shown in the dashboard panels
     * @var integer
     */
    protected $dashboard_limit;

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
    public function __construct(
        ModelsProduct $product,
        ModelsPrice $price,
        ModelsOrder $order,
        ModelsReport $report,
        ModelsAnalytics $analytics,
        ModelsReview $review
    ) {
        parent::__construct();

        $this->price = $price;
        $this->order = $order;
        $this->report = $report;
        $this->review = $review;
        $this->product = $product;
        $this->analytics = $analytics;

        $this->dashboard_limit = (int)$this->config('dashboard_limit', 10);
    }

    /**
     * Displays the admin dashboard page
     */
    public function dashboard()
    {
        $this->toggleIntroDashboard();

        $this->setDataGaDashboard();
        $this->setDataUsersDashboard();
        $this->setDataOrdersDashboard();
        $this->setDataEventsDashboard();
        $this->setDataSummaryDashboard();

        $this->setJsDashboard();

        $this->setTitleDashboard();
        $this->outputDashboard();
    }

    /**
     * Toggles dashboard page from post-installation intro to normal view
     */
    protected function toggleIntroDashboard()
    {
        if ($this->isQuery('skip_intro')) {
            $this->config->reset('intro');
            $this->redirect();
        }
    }

    /**
     * Sets Google Analytics panel
     * @return null
     */
    protected function setDataGaDashboard()
    {
        $gapi_email = $this->config('gapi_email', '');
        $gapi_certificate = $this->config('gapi_certificate', '');

        $default = $this->store->getDefault();
        $store_id = $this->request->get('store_id', $default);
        $store = $this->store->get($store_id);
        $stores = $this->store->getList();

        $data = array(
            'store' => $store,
            'stores' => $stores,
            'chart_traffic' => array(),
            'missing_settings' => empty($store['data']['ga_view']),
            'missing_credentials' => (empty($gapi_email) || empty($gapi_certificate))
        );

        if (!$data['missing_settings']) {
            $data['ga_view'] = $store['data']['ga_view'];
        }

        if ($this->isQuery('ga_update') && $this->access('report_ga') && !empty($data['ga_view'])) {
            $this->report->clearGaCache($data['ga_view']);
            $this->redirect();
        }

        if (!$data['missing_credentials'] && !$data['missing_settings']) {
            $this->analytics->setCredentials($gapi_email, $gapi_certificate, "Analytics for {$store['domain']}");
            $this->analytics->setView($data['ga_view']);
            $chart = $this->report->buildTrafficChart($this->analytics);
            $this->setJsSettings('chart_traffic', $chart);
        }

        $html = $this->render('dashboard/panels/ga', $data);
        $this->setData('dashboard_panel_ga_chart', $html);
    }

    /**
     * Sets recent users panel
     */
    protected function setDataUsersDashboard()
    {
        $options = array(
            'limit' => array(0, $this->dashboard_limit)
        );

        $users = $this->user->getList($options);

        $html = $this->render('dashboard/panels/users', array('users' => $users));
        $this->setData('dashboard_panel_users', $html);
    }

    /**
     * Sets recent orders panel
     */
    protected function setDataOrdersDashboard()
    {
        $options = array('limit' => array(0, $this->dashboard_limit));

        $orders = $this->order->getList($options);

        array_walk($orders, function (&$order) {
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
            $order['rendered'] = $this->render('search/suggestion/order', array('order' => $order));
        });

        $html = $this->render('dashboard/panels/orders', array('orders' => $orders));
        $this->setData('dashboard_panel_orders', $html);
    }

    /**
     * Sets recent events panel
     */
    protected function setDataEventsDashboard()
    {
        $events = array();
        $severities = $this->report->getSeverities();

        foreach (array_keys($severities) as $severity) {

            $options = array(
                'severity' => $severity,
                'limit' => array(0, $this->dashboard_limit)
            );

            $items = $this->report->getList($options);

            foreach ($items as &$item) {
                $variables = empty($item['data']['variables']) ? array() : (array)$item['data']['variables'];
                $message = empty($item['translatable']) ? $item['text'] : $this->text($item['text'], $variables);
                $item['message'] = strip_tags($message);
            }

            if (empty($items)) {
                continue;
            }

            $events[$severity] = $items;
        }

        $this->setJsChartEventsDashboard();

        $html = $this->render('dashboard/panels/events', array('events' => $events));
        $this->setData('dashboard_panel_events', $html);
    }

    /**
     * Sets JS settings for events chart
     */
    protected function setJsChartEventsDashboard()
    {
        $allowed = array(
            'info' => '#36A2EB',
            'danger' => '#FF6384',
            'warning' => '#FFCE56'
        );

        $counted = $this->report->countSeverity();
        $results = array_filter(array_intersect_key($counted, $allowed));

        $chart = array(
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false
            )
        );

        $i = 0;
        foreach ($results as $severity => $count) {
            $chart['labels'][$i] = $severity;
            $chart['datasets'][0]['data'][$i] = $count;
            $chart['datasets'][0]['backgroundColor'][$i] = $allowed[$severity];
            $i++;
        }

        $this->setJsSettings('chart_events', $chart);
    }

    /**
     * Sets summary panel
     */
    protected function setDataSummaryDashboard()
    {
        $data = array(
            'user_total' => $this->user->getList(array('count' => true)),
            'order_total' => $this->order->getList(array('count' => true)),
            'review_total' => $this->review->getList(array('count' => true)),
            'product_total' => $this->product->getList(array('count' => true))
        );

        $html = $this->render('dashboard/panels/summary', $data);
        $this->setData('dashboard_panel_summary', $html);
    }

    /**
     * Sets Java Scripts on the dashboard page
     */
    protected function setJsDashboard()
    {
        $this->setJs('files/assets/chart/Chart.min.js', 'top');
    }

    /**
     * Sets titles on the admin dashboard page
     */
    protected function setTitleDashboard()
    {
        $this->setTitle($this->text('Dashboard'), false);
    }

    /**
     * Renders the admin dashboard page
     */
    protected function outputDashboard()
    {
        $intro = (bool)$this->config('intro', 0);

        if ($intro && $this->isSuperadmin()) {
            $this->output('dashboard/intro');
        }

        $this->output('dashboard/dashboard');
    }

}
