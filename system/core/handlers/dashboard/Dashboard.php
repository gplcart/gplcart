<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\dashboard;

use gplcart\core\Handler,
    gplcart\core\Config;
use gplcart\core\models\Cart as CartModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Order as OrderModel,
    gplcart\core\models\OrderHistory as OrderHistoryModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Report as ReportModel,
    gplcart\core\models\Review as ReviewModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\PriceRule as PriceRuleModel,
    gplcart\core\models\Transaction as TransactionModel;
use gplcart\core\traits\ItemPrice as ItemPriceTrait,
    gplcart\core\traits\ItemOrder as ItemOrderTrait;

/**
 * Dashboard handlers
 */
class Dashboard extends Handler
{

    use ItemPriceTrait,
        ItemOrderTrait;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

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
     * Order history model class instance
     * @var \gplcart\core\models\OrderHistory $order_history
     */
    protected $order_history;

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
     * Review model instance
     * @var \gplcart\core\models\Review $review
     */
    protected $review;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Config $config
     * @param CartModel $cart
     * @param UserModel $user
     * @param ProductModel $product
     * @param TranslationModel $translation
     * @param PriceModel $price
     * @param OrderModel $order
     * @param OrderHistoryModel $order_history
     * @param ReportModel $report
     * @param ReviewModel $review
     * @param TransactionModel $transaction
     * @param PriceRuleModel $pricerule
     */
    public function __construct(Config $config, CartModel $cart, UserModel $user,
            ProductModel $product, TranslationModel $translation, PriceModel $price, OrderModel $order,
            OrderHistoryModel $order_history, ReportModel $report, ReviewModel $review,
            TransactionModel $transaction, PriceRuleModel $pricerule)
    {
        $this->cart = $cart;
        $this->user = $user;
        $this->price = $price;
        $this->order = $order;
        $this->report = $report;
        $this->config = $config;
        $this->review = $review;
        $this->product = $product;
        $this->pricerule = $pricerule;
        $this->transaction = $transaction;
        $this->translation = $translation;
        $this->order_history = $order_history;
    }

    /**
     * Returns an array of summary items
     * @return array
     */
    public function summary()
    {
        $options = array('count' => true);

        return array(
            'user_total' => $this->user->getList($options),
            'order_total' => $this->order->getList($options),
            'review_total' => $this->review->getList($options),
            'product_total' => $this->product->getList($options)
        );
    }

    /**
     * Returns an array of recent orders
     * @return array
     */
    public function order()
    {
        $options = array(
            'order' => 'desc',
            'sort' => 'created',
            'limit' => array(0, $this->config->get('dashboard_limit', 10)));

        $items = $this->order->getList($options);

        array_walk($items, function (&$item) {
            $this->setItemTotalFormatted($item, $this->price);
            $this->setItemOrderNew($item, $this->order_history);
        });

        return $items;
    }

    /**
     * Returns an array of recent transactions
     * @return array
     */
    public function transaction()
    {
        $options = array(
            'order' => 'desc',
            'sort' => 'created',
            'limit' => array(0, $this->config->get('dashboard_limit', 10)));

        $items = $this->transaction->getList($options);

        array_walk($items, function (&$item) {
            $this->setItemTotalFormatted($item, $this->price);
        });

        return $items;
    }

    /**
     * Returns an array of active price rules
     * @return array
     */
    public function priceRule()
    {
        $options = array(
            'status' => 1,
            'order' => 'desc',
            'sort' => 'created',
            'limit' => array(0, $this->config->get('dashboard_limit', 10)));

        $items = $this->pricerule->getList($options);

        array_walk($items, function (&$item) {
            $item['value_formatted'] = $this->price->format($item['value'], $item['currency']);
        });

        return $items;
    }

    /**
     * Returns an array of recent cart items
     * @return array
     */
    public function cart()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config->get('dashboard_limit', 10)));

        return $this->cart->getList($options);
    }

    /**
     * Returns an array of recent users
     * @return array
     */
    public function user()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config->get('dashboard_limit', 10))
        );

        return $this->user->getList($options);
    }

    /**
     * Returns an array of recent reviews
     * @return array
     */
    public function review()
    {
        $options = array(
            'sort' => 'created',
            'order' => 'desc',
            'limit' => array(0, $this->config->get('dashboard_limit', 10)));

        return $this->review->getList($options);
    }

    /**
     * Returns an array of recent events
     * @return array
     */
    public function event()
    {
        $items = array();
        foreach (array_keys($this->report->getSeverities()) as $severity) {

            $options = array(
                'severity' => $severity,
                'limit' => array(0, $this->config->get('dashboard_limit', 10))
            );

            $events = (array) $this->report->getList($options);

            if (empty($events)) {
                continue;
            }

            foreach ($events as &$event) {
                $variables = empty($event['data']['variables']) ? array() : (array) $event['data']['variables'];
                $message = empty($event['translatable']) ? $event['text'] : $this->translation->text($event['text'], $variables);
                $event['message'] = strip_tags($message);
            }

            $items[$severity] = $events;
        }

        return $items;
    }

}
