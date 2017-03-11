<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Report as ReportModel,
    gplcart\core\models\Review as ReviewModel,
    gplcart\core\models\Product as ProductModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to admin dashboard
 */
class Dashboard extends BackendController
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * Max items to be shown in the dashboard panels
     * @var integer
     */
    protected $dashboard_limit;

    /**
     * Review model instance
     * @var \gplcart\core\models\Review $review
     */
    protected $review;

    /**
     * Constructor
     * @param ProductModel $product
     * @param PriceModel $price
     * @param OrderModel $order
     * @param ReportModel $report
     * @param ReviewModel $review
     */
    public function __construct(ProductModel $product, PriceModel $price,
            OrderModel $order, ReportModel $report, ReviewModel $review
    )
    {
        parent::__construct();

        $this->price = $price;
        $this->order = $order;
        $this->report = $report;
        $this->review = $review;
        $this->product = $product;

        $this->dashboard_limit = (int) $this->config('dashboard_limit', 10);
    }

    /**
     * Displays the admin dashboard page
     */
    public function dashboard()
    {
        $this->toggleIntroDashboard();

        $this->setTitleDashboard();

        $this->setDataUsersDashboard();
        $this->setDataOrdersDashboard();
        $this->setDataEventsDashboard();
        $this->setDataSummaryDashboard();

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
            $order['is_new'] = $this->order->isNew($order);
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
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

            $items = (array) $this->report->getList($options);

            foreach ($items as &$item) {
                $variables = empty($item['data']['variables']) ? array() : (array) $item['data']['variables'];
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
        $options = array('count' => true);

        $data = array(
            'user_total' => $this->user->getList($options),
            'order_total' => $this->order->getList($options),
            'review_total' => $this->review->getList($options),
            'product_total' => $this->product->getList($options)
        );

        $html = $this->render('dashboard/panels/summary', $data);
        $this->setData('dashboard_panel_summary', $html);
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
        $intro = (bool) $this->config('intro', 0);

        if ($intro && $this->isSuperadmin()) {
            $this->output('dashboard/intro');
        }

        $this->output('dashboard/dashboard');
    }

}
