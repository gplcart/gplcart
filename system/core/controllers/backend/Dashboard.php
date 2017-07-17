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
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\PriceRule as PriceRuleModel,
    gplcart\core\models\Transaction as TransactionModel;
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
     * Transaction model instance
     * @var \gplcart\core\models\Transaction $transaction
     */
    protected $transaction;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $pricerule;

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
     * @param ProductModel $product
     * @param PriceModel $price
     * @param OrderModel $order
     * @param ReportModel $report
     * @param ReviewModel $review
     * @param TransactionModel $transaction
     * @param PriceRuleModel $pricerule
     */
    public function __construct(ProductModel $product, PriceModel $price,
            OrderModel $order, ReportModel $report, ReviewModel $review,
            TransactionModel $transaction, PriceRuleModel $pricerule)
    {
        parent::__construct();

        $this->price = $price;
        $this->order = $order;
        $this->report = $report;
        $this->review = $review;
        $this->product = $product;
        $this->pricerule = $pricerule;
        $this->transaction = $transaction;
    }

    /**
     * Displays the dashboard page
     */
    public function indexDashboard()
    {
        $this->toggleIntroIndexDashboard();
        $this->setTitleIndexDashboard();
        $this->setDataContentIndexDashboard();
        $this->outputIndexDashboard();
    }

    /**
     * Sets a dashboard content data
     */
    protected function setDataContentIndexDashboard()
    {
        $panels = $this->getPanelsDashboard();
        $columns = $this->config('dashboard_columns', 2);
        $splitted = gplcart_array_split($panels, $columns);

        $this->setData('columns', $columns);
        $this->setData('dashboard', $splitted);

        if ($this->config('intro', false) && $this->isSuperadmin()) {
            $items = $this->getIntroItemsDashboard();
            $this->setData('intro', $this->render('dashboard/intro', array('items' => $items)));
        }
    }

    /**
     * Returns an array of dashboard intro items
     */
    protected function getIntroItemsDashboard()
    {
        $items = array();
        foreach (array('header', 'settings', 'product', 'module') as $i => $item) {
            $items[$item] = array('weight' => $i, 'rendered' => $this->render("dashboard/intro/$item"));
        }

        $this->hook->fire('dashboard.intro', $items, $this);
        gplcart_array_sort($items);
        return $items;
    }

    /**
     * Returns an array of sorted panels
     * @return array
     */
    protected function getPanelsDashboard()
    {
        $panels = $this->getDefaultPanelsDashboard();
        $this->hook->fire('dashboard.panels', $panels, $this);
        gplcart_array_sort($panels);
        return $panels;
    }

    /**
     * Returns an array of default dashboard panels
     * @return array
     */
    protected function getDefaultPanelsDashboard()
    {
        $panels = array();
        $panels['summary'] = array('rendered' => $this->renderPanelSummaryDashboard(), 'weight' => 1);
        $panels['order'] = array('rendered' => $this->renderPanelOrdersDashboard(), 'weight' => 2);
        $panels['transaction'] = array('rendered' => $this->renderPanelTransactionDashboard(), 'weight' => 3);
        $panels['pricerule'] = array('rendered' => $this->renderPanelPriceRuleDashboard(), 'weight' => 4);
        $panels['cart'] = array('rendered' => $this->renderPanelCartDashboard(), 'weight' => 5);
        $panels['user'] = array('rendered' => $this->renderPanelUsersDashboard(), 'weight' => 6);
        $panels['review'] = array('rendered' => $this->renderPanelReviewsDashboard(), 'weight' => 7);
        $panels['event'] = array('rendered' => $this->renderPanelEventsDashboard(), 'weight' => 8);

        return $panels;
    }

    /**
     * Toggles intro view
     */
    protected function toggleIntroIndexDashboard()
    {
        if ($this->isQuery('skip_intro')) {
            $this->config->reset('intro');
            $this->redirect();
        }
    }

    /**
     * Returns the rendered recent users panel
     * @return string
     */
    protected function renderPanelUsersDashboard()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config('dashboard_limit', 10))
        );

        $items = $this->user->getList($options);
        return $this->render('dashboard/panels/users', array('items' => $items));
    }

    /**
     * Returns the rendered recent orders panel
     * @return string
     */
    protected function renderPanelOrdersDashboard()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config('dashboard_limit', 10)));

        $items = $this->order->getList($options);

        array_walk($items, function (&$item) {
            $item['is_new'] = $this->order->isNew($item);
            $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);
        });

        return $this->render('dashboard/panels/orders', array('items' => $items));
    }

    /**
     * Returns the rendered recent events panel
     * @return string
     */
    protected function renderPanelEventsDashboard()
    {
        $items = array();
        foreach (array_keys($this->report->getSeverities()) as $severity) {

            $options = array(
                'severity' => $severity,
                'limit' => array(0, $this->config('dashboard_limit', 10))
            );

            $events = (array) $this->report->getList($options);

            if (empty($events)) {
                continue;
            }

            foreach ($events as &$event) {
                $variables = empty($event['data']['variables']) ? array() : (array) $event['data']['variables'];
                $message = empty($event['translatable']) ? $event['text'] : $this->text($event['text'], $variables);
                $event['message'] = strip_tags($message);
            }

            $items[$severity] = $events;
        }

        return $this->render('dashboard/panels/events', array('items' => $items));
    }

    /**
     * Returns the rendered summary panel
     * @return string
     */
    protected function renderPanelSummaryDashboard()
    {
        $options = array('count' => true);

        $data = array(
            'user_total' => $this->user->getList($options),
            'order_total' => $this->order->getList($options),
            'review_total' => $this->review->getList($options),
            'product_total' => $this->product->getList($options)
        );

        return $this->render('dashboard/panels/summary', $data);
    }

    /**
     * Returns the rendered cart items panel
     * @return string
     */
    protected function renderPanelCartDashboard()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config('dashboard_limit', 10)));

        $items = $this->cart->getList($options);
        return $this->render('dashboard/panels/cart', array('items' => $items));
    }

    /**
     * Returns the rendered transactions panel
     * @return string
     */
    protected function renderPanelTransactionDashboard()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config('dashboard_limit', 10)));

        $items = $this->transaction->getList($options);

        array_walk($items, function (&$item) {
            $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);
        });

        return $this->render('dashboard/panels/transactions', array('items' => $items));
    }

    /**
     * Returns the rendered price rules panel
     * @return string
     */
    protected function renderPanelPriceRuleDashboard()
    {
        $options = array(
            'status' => 1,
            'trigger_status' => 1,
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config('dashboard_limit', 10)));

        $items = $this->pricerule->getList($options);

        array_walk($items, function (&$item) {
            $item['value_formatted'] = $this->price->format($item['value'], $item['currency']);
        });

        return $this->render('dashboard/panels/pricerules', array('items' => $items));
    }

    /**
     * Returns the rendered reviews panel
     * @return string
     */
    protected function renderPanelReviewsDashboard()
    {

        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config('dashboard_limit', 10)));

        $items = $this->review->getList($options);
        return $this->render('dashboard/panels/reviews', array('items' => $items));
    }

    /**
     * Sets titles on the dashboard page
     */
    protected function setTitleIndexDashboard()
    {
        $this->setTitle($this->text('Dashboard'), false);
    }

    /**
     * Render and output the dashboard page
     */
    protected function outputIndexDashboard()
    {
        $this->output('dashboard/dashboard');
    }

}
