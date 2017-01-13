<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Cart as CartModel;
use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Provides data to the view and interprets user actions related to orders
 */
class Order extends BackendController
{

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

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
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * The current order
     * @var array
     */
    protected $data_order = array();

    /**
     * Constructor
     * @param OrderModel $order
     * @param CountryModel $country
     * @param StateModel $state
     * @param AddressModel $address
     * @param PriceModel $price
     * @param CurrencyModel $currency
     * @param CartModel $cart
     * @param ProductModel $product
     * @param PriceRuleModel $pricerule
     */
    public function __construct(OrderModel $order, CountryModel $country,
            StateModel $state, AddressModel $address, PriceModel $price,
            CurrencyModel $currency, CartModel $cart, ProductModel $product,
            PriceRuleModel $pricerule, PaymentModel $payment,
            ShippingModel $shipping
    )
    {
        parent::__construct();

        $this->cart = $cart;
        $this->state = $state;
        $this->order = $order;
        $this->price = $price;
        $this->product = $product;
        $this->country = $country;
        $this->address = $address;
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->currency = $currency;
        $this->pricerule = $pricerule;
    }

    /**
     * Displays the order snapshot page
     * @param integer $order_id
     */
    public function snapshotOrder($order_id)
    {
        $this->setLogOrder($order_id);

        $this->setTitleSnapshotOrder();
        $this->setBreadcrumbSnapshotOrder();

        $this->setDataOrder();
        $this->setMessageSnapshotOrder();
        $this->outputSnapshotOrder();
    }

    /**
     * Sets a message on the order snapshot page
     */
    protected function setMessageSnapshotOrder()
    {
        $vars = array(
            '@order_id' => $this->data_order['order_id'],
            '@url' => $this->url("admin/sale/order/{$this->data_order['order_id']}")
        );

        $message = $this->text('This is a saved snapshot of order #@order_id. You can see current state of the order <a href="@url">here</a>', $vars);
        $this->setMessage($message, 'warning');
    }

    /**
     * Sets titles on the order snapshot page
     */
    protected function setTitleSnapshotOrder()
    {
        $vars = array('@order_id' => $this->data_order['order_id']);
        $title = $this->text('Snapshot of order #@order_id', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the order snapshot page
     */
    protected function setBreadcrumbSnapshotOrder()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Orders'),
            'url' => $this->url('admin/sale/order')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Order #@order_id', array(
                '@order_id' => $this->data_order['order_id'])),
            'url' => $this->url("admin/sale/order/{$this->data_order['order_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders order snapshot templates
     */
    protected function outputSnapshotOrder()
    {
        $this->output('sale/order/order');
    }

    /**
     * Returns an order log
     * @param integer $order_id
     * @return array
     */
    protected function setLogOrder($order_id)
    {
        $log = $this->order->getLog($order_id);

        if (empty($log['data'])) {
            $this->outputHttpStatus(404);
        }

        $order = $log['data'];
        unset($log['data']);
        $order['log'] = $log;

        $this->data_order = $this->prepareOrder($order);
        return $this->data_order;
    }

    /**
     * Displays the order overview page
     * @param integer $order_id
     */
    public function viewOrder($order_id)
    {
        $this->setOrder($order_id);

        $this->setTitleViewOrder();
        $this->setBreadcrumbViewOrder();

        $this->order->setViewed($this->data_order);

        $this->setDataOrder();
        $this->outputViewOrder();
    }

    /**
     * Sets teplate data to be used on the order overview page
     */
    protected function setDataOrder()
    {
        $this->setData('order', $this->data_order);

        $this->setDataLogsOrder();
        $this->setDataSummaryOrder();
        $this->setDataCustomerOrder();
        $this->setDataComponentsOrder();
        $this->setDataShippingAddressOrder();
    }

    /**
     * Sets logs pane on the order overview page
     */
    protected function setDataLogsOrder()
    {
        $query = $this->getFilterQuery();
        $total = $this->getTotalLogOrder();

        $max = $this->config('order_log_limit', 5);
        $limit = $this->setPager($total, $query, $max);
        $items = $this->getListLogOrder($limit);

        $data = array(
            'items' => $items,
            'order' => $this->data_order,
            'pager' => $this->getPager()
        );

        $html = $this->render('sale/order/panes/log', $data);
        $this->setData('pane_log', $html);
    }

    /**
     * Returns a total logs found for the order
     * @return integer
     */
    protected function getTotalLogOrder()
    {
        $options = array(
            'count' => true,
            'order_id' => $this->data_order['order_id']
        );

        return (int) $this->order->getLogList($options);
    }

    /**
     * Returns an array of log records for the order
     * @param array $limit
     * @return array
     */
    protected function getListLogOrder(array $limit)
    {
        $options = array(
            'limit' => $limit,
            'order_id' => $this->data_order['order_id']
        );

        return (array) $this->order->getLogList($options);
    }

    /**
     * Renders order overview page templates
     */
    protected function outputViewOrder()
    {
        $this->output('sale/order/order');
    }

    /**
     * Sets titles on the order overview page
     */
    protected function setTitleViewOrder()
    {
        $vars = array('@order_id' => $this->data_order['order_id']);
        $title = $this->text('Order #@order_id', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the order overview page
     */
    protected function setBreadcrumbViewOrder()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Orders'),
            'url' => $this->url('admin/sale/order')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Returns an order
     * @param integer $order_id
     * @return array
     */
    protected function setOrder($order_id)
    {
        if (!is_numeric($order_id)) {
            return array();
        }

        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        $this->data_order = $this->prepareOrder($order);
        return $this->data_order;
    }

    /**
     * Adds some extra data to the order array
     * @param array $order
     * @return array
     */
    protected function prepareOrder(array $order)
    {
        $store = $this->store->get($order['store_id']);

        if (!empty($store['name'])) {
            $order['store_name'] = $store['name'];
        }

        $order['customer'] = $this->text('Anonymous');
        $order['creator_formatted'] = $this->text('Customer');
        $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);

        if (!empty($order['creator'])) {
            $order['creator_formatted'] = $this->text('Unknown');
            $user = $this->user->get($order['user_id']);
            if (isset($user['user_id'])) {
                $order['creator_formatted'] = "{$user['name']} ({$user['email']})";
            }
        }

        return $order;
    }

    /**
     * Sets summary pane on the order overview page
     */
    protected function setDataSummaryOrder()
    {
        $data = array(
            'order' => $this->data_order,
            'statuses' => $this->order->getStatuses(),
        );

        $html = $this->render('sale/order/panes/summary', $data);
        $this->setData('pane_summary', $html);
    }

    /**
     * Sets customer pane on the order overview page
     */
    protected function setDataCustomerOrder()
    {
        $user_id = $this->data_order['user_id'];

        $user = null;
        if (is_numeric($user_id)) {
            $user = $this->user->get($user_id);
        }

        $data = array(
            'user' => $user,
            'order' => $this->data_order,
            'placed' => $this->getTotalPlacedOrder($user_id),
        );

        $html = $this->render('sale/order/panes/customer', $data);
        $this->setData('pane_customer', $html);
    }

    /**
     * Returns a number of orders placed by a given user
     * @param integer|string $user_id
     * @return integer
     */
    protected function getTotalPlacedOrder($user_id)
    {
        $options = array(
            'count' => true,
            'user_id' => $user_id
        );

        return (int) $this->order->getList($options);
    }

    /**
     * Sets order components pane on the order overview page
     */
    protected function setDataComponentsOrder()
    {
        $components = $this->getComponentsOrder();

        $data = array('components' => $components);
        $html = $this->render('sale/order/panes/components', $data);
        $this->setData('pane_components', $html);
    }

    /**
     * Returns an array of prepared order components
     * @return array
     */
    protected function getComponentsOrder()
    {
        if (empty($this->data_order['data']['components'])) {
            return array();
        }

        $components = array();
        foreach ($this->data_order['data']['components'] as $type => $value) {
            $this->setComponentCartOrder($components, $type, $value);
            $this->setComponentMethodOrder($components, $type, $value);
            $this->setComponentRuleOrder($components, $type, $value);
        }

        ksort($components);
        return $components;
    }

    /**
     * Sets rendered component "Cart"
     * @param array $components
     * @param string $type
     * @param array $component_cart
     * @return null
     */
    protected function setComponentCartOrder(array &$components, $type,
            $component_cart)
    {
        if ($type !== 'cart') {
            return null;
        }

        foreach ($component_cart as $sku => $price) {
            $this->validateCarProductOrder($sku);
            $this->data_order['cart'][$sku]['price_formatted'] = $this->price->format($price, $this->data_order['currency']);
        }

        $html = $this->render('sale/order/panes/components/cart', array('order' => $this->data_order));
        $components['cart'] = $html;
    }

    /**
     * Checks cart products and sets notifications for order manager
     * @param string $sku
     * @return null
     * @todo move to validator handlers
     */
    protected function validateCarProductOrder($sku)
    {
        if (empty($this->data_order['cart'][$sku]) || empty($this->data_order['cart'][$sku]['product_id'])) {
            $message = $this->text('SKU %sku is invalid', array('%sku' => $sku));
            $this->setMessage($message, 'warning');
            return null; // Exit here to avoid "undefined" errors below
        }

        if (empty($this->data_order['cart'][$sku]['product_status'])) {
            $vars = array('%product_id' => $this->data_order['cart'][$sku]['product_id']);
            $message = $this->text('Product %product_id is disabled', $vars);
            $this->setMessage($message, 'warning');
        }

        if ($this->data_order['cart'][$sku]['product_store_id'] != $this->data_order['store_id']) {
            $vars = array('%product_id' => $this->data_order['cart'][$sku]['product_id']);
            $message = $this->text('Product %product_id does not belong to the order\'s store', $vars);
            $this->setMessage($message, 'warning');
        }
    }

    /**
     * Sets rendered shipping/payment component
     * @param array $components
     * @param string $type
     * @param integer $price
     * @return null
     */
    protected function setComponentMethodOrder(&$components, $type, $price)
    {
        if (!in_array($type, array('shipping', 'payment'))) {
            return null;
        }

        $method = $this->{$type}->get();
        $method['name'] = isset($method['name']) ? $method['name'] : $this->text('Unknown');

        if (abs($price) == 0) {
            $price = 0;
        }

        $method['price_formatted'] = $this->price->format($price, $this->data_order['currency']);
        $html = $this->render('sale/order/panes/components/method', array('method' => $method));
        $components[$type] = $html;
    }

    /**
     * Sets rendered rule component
     * @param array $components
     * @param string $rule_id
     * @param integer $price
     * @return null
     */
    protected function setComponentRuleOrder(&$components, $rule_id, $price)
    {
        if (!is_numeric($rule_id)) {
            return null;
        }

        if (abs($price) == 0) {
            $price = 0; // Avoid something like -0 USD
        }

        $rule = $this->pricerule->get($rule_id);

        $data = array(
            'rule' => $rule,
            'price' => $this->price->format($price, $rule['currency'])
        );

        $html = $this->render('sale/order/panes/components/rule', $data);
        $components["rule_$rule_id"] = $html;
        return null;
    }

    /**
     * Returns rendered shipping address pane
     */
    protected function setDataShippingAddressOrder()
    {
        $address = $this->address->get($this->data_order['shipping_address']);
        $translated = $this->address->getTranslated($address);
        $geocode = $this->address->getGeocodeQuery($translated);

        $map = array(
            'address' => $geocode,
            'key' => $this->config('gapi_browser_key', '')
        );

        $this->setJsSettings('map', $map);

        $data = array(
            'order' => $this->data_order,
            'address' => $address,
            'items' => $this->address->getTranslated($address, true)
        );

        $html = $this->render('sale/order/panes/shipping_address', $data);
        $this->setData('pane_shipping_address', $html);
    }

    /**
     * Displays the order admin overview page
     */
    public function listOrder()
    {
        $this->actionOrder();

        $this->setTitleListOrder();
        $this->setBreadcrumbListOrder();

        $this->setData('stores', $this->store->getNames());
        $this->setData('statuses', $this->store->getNames());
        $this->setData('currencies', $this->currency->getList());

        $query = $this->getFilterQuery();

        $allowed = array('store_id', 'order_id', 'status', 'created',
            'creator', 'user_id', 'total', 'currency');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalOrder($query);
        $limit = $this->setPager($total, $query);
        $orders = $this->getListOrder($limit, $query);

        $this->setData('orders', $orders);
        $this->outputListOrder();
    }

    /**
     * Applies an action to the selected orders
     * @return null
     */
    protected function actionOrder()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (string) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('order_edit')) {
                $updated += (int) $this->order->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('order_delete')) {
                $deleted += (int) $this->order->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Orders have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Orders have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets titles on the orders overview page
     */
    protected function setTitleListOrder()
    {
        $this->setTitle($this->text('Orders'));
    }

    /**
     * Sets breadcrumbs on the orders overview page
     */
    protected function setBreadcrumbListOrder()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders overview orders page templates
     */
    protected function outputListOrder()
    {
        $this->output('sale/order/list');
    }

    /**
     * Returns total number of orders for pages
     * @param array $query
     * @return integer
     */
    protected function getTotalOrder(array $query)
    {
        $query['count'] = true;
        return (int) $this->order->getList($query);
    }

    /**
     * Returns an array of orders
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListOrder($limit, array $query)
    {
        $query['limit'] = $limit;
        $orders = (array) $this->order->getList($query);
        return $this->prepareListOrder($orders);
    }

    /**
     * Modifies an array of orders
     * @param array $orders
     * @return array
     */
    protected function prepareListOrder(array $orders)
    {
        foreach ($orders as &$order) {
            $order['is_new'] = $this->order->isNew($order);
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        }

        return $orders;
    }

}
