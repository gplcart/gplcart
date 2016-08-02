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

        $this->dashboard_limit = (int) $this->config->get('dashboard_limit', 10);
    }

    /**
     * Displays the admin dashboard page
     */
    public function dashboard()
    {
        if ($this->request->get('skip_intro')) {
            $this->config->reset('intro');
            $this->redirect();
        }

        $this->setPanelGa();
        $this->setPanelUsers();
        $this->setPanelOrders();
        $this->setPanelEvents();
        $this->setPanelSummary();

        $this->setJsDashboard();

        $this->setTitleDashboard();
        $this->outputDashboard();
    }

    /**
     * Sets Java Scripts on the dashboard page
     */
    protected function setJsDashboard()
    {
        $this->setJs('files/assets/chart/Chart.min.js', 'top');
    }

    /**
     * Renders the admin dashboard page
     */
    protected function outputDashboard()
    {
        $intro = (bool) $this->config->get('intro', 0);

        if ($intro && $this->isSuperadmin()) {
            $this->output('dashboard/intro');
        }

        $this->output('dashboard/dashboard');
    }

    /**
     * Sets titles on the admin dashboard page
     */
    protected function setTitleDashboard()
    {
        $this->setTitle($this->text('Dashboard'), false);
    }

    /**
     * Sets Google Analytics panel
     * @return null
     */
    protected function setPanelGa()
    {
        $gapi_email = $this->config->get('gapi_email', '');
        $gapi_certificate = $this->config->get('gapi_certificate', '');

        $store_id = $this->request->get('store_id', $this->store->getDefault());
        $store = $this->store->get($store_id);

        $data = array(
            'chart_traffic' => array(),
            'stores' => $this->store->getList(),
            'store' => $store,
            'missing_settings' => empty($store['data']['ga_view']),
            'missing_credentials' => (empty($gapi_email) || empty($gapi_certificate))
        );

        if (!$data['missing_settings']) {
            $data['ga_view'] = $store['data']['ga_view'];
        }

        if ($this->request->get('ga_update') && $this->access('report_ga') && !empty($data['ga_view'])) {
            $this->report->clearGaCache($data['ga_view']);
            $this->redirect();
        }

        if (!$data['missing_credentials'] && !$data['missing_settings']) {
            $this->analytics->setCredentials($gapi_email, $gapi_certificate, "Analytics for {$store['domain']}");
            $this->analytics->setView($data['ga_view']);
            $chart = $this->report->buildTrafficChart($this->analytics);
            $this->setJsSettings('chart_traffic', $chart);
        }

        $this->data['dashboard_panel_ga_chart'] = $this->render('dashboard/panels/ga', $data);
    }

    /**
     * Sets summary panel
     */
    protected function setPanelSummary()
    {
        $data = array(
            'user_total' => $this->user->getList(array('count' => true)),
            'order_total' => $this->order->getList(array('count' => true)),
            'review_total' => $this->review->getList(array('count' => true)),
            'product_total' => $this->product->getList(array('count' => true))
        );

        $this->data['dashboard_panel_summary'] = $this->render('dashboard/panels/summary', $data);
    }

    /**
     * Sets recent users panel
     */
    protected function setPanelUsers()
    {
        $users = $this->user->getList(array('limit' => array(0, $this->dashboard_limit)));
        $this->data['dashboard_panel_users'] = $this->render('dashboard/panels/users', array('users' => $users));
    }

    /**
     * Sets recent orders panel
     */
    protected function setPanelOrders()
    {
        $orders = $this->order->getList(array('limit' => array(0, $this->dashboard_limit)));

        array_walk($orders, function (&$order) {
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
            $order['html'] = $this->render('settings/search/suggestion/order', array('order' => $order));
        });

        $this->data['dashboard_panel_orders'] = $this->render('dashboard/panels/orders', array('orders' => $orders));
    }

    /**
     * Sets recent events panel
     */
    protected function setPanelEvents()
    {
        $events = array();
        foreach (array('info', 'warning', 'danger') as $severity) {

            $items = $this->report->getList(array(
                'limit' => array(0, $this->dashboard_limit),
                'severity' => $severity));

            foreach ($items as $i => &$item) {
                $variables = empty($item['data']['variables']) ? array() : (array) $item['data']['variables'];
                $message = $this->text($item['text'], $variables);
                $item['message'] = strip_tags($message);
            }

            if (!empty($items)) {
                $events[$severity] = $items;
            }
        }

        $this->setJsChartEvents();

        $this->data['dashboard_panel_events'] = $this->render('dashboard/panels/events', array('events' => $events));
    }

    /**
     * Sets JS settings for events chart
     */
    protected function setJsChartEvents()
    {
        $allowed = array('danger' => '#FF6384', 'warning' => '#FFCE56', 'info' => '#36A2EB');
        $results = array_filter(array_intersect_key($this->report->countSeverity(), $allowed));

        $chart = array('options' => array('maintainAspectRatio' => false, 'responsive' => true));

        $i = 0;
        foreach ($results as $severity => $count) {
            $chart['labels'][$i] = $severity;
            $chart['datasets'][0]['data'][$i] = $count;
            $chart['datasets'][0]['backgroundColor'][$i] = $allowed[$severity];
            $i++;
        }

        $this->setJsSettings('chart_events', $chart);
    }

}
