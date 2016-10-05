<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\controllers\admin\Controller as BackendController;
use core\models\Address as ModelsAddress;
use core\models\Cart as ModelsCart;
use core\models\Country as ModelsCountry;
use core\models\Currency as ModelsCurrency;
use core\models\Order as ModelsOrder;
use core\models\Price as ModelsPrice;
use core\models\PriceRule as ModelsPriceRule;
use core\models\Product as ModelsProduct;
use core\models\State as ModelsState;

/**
 * Provides data to the view and interprets user actions related to orders
 */
class Order extends BackendController
{

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Cart model instance
     * @var \core\models\Cart $cart
     */
    protected $cart;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ModelsOrder $order
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsAddress $address
     * @param ModelsPrice $price
     * @param ModelsCurrency $currency
     * @param ModelsCart $cart
     * @param ModelsProduct $product
     * @param ModelsPriceRule $pricerule
     */
    public function __construct(
    ModelsOrder $order, ModelsCountry $country, ModelsState $state,
            ModelsAddress $address, ModelsPrice $price,
            ModelsCurrency $currency, ModelsCart $cart, ModelsProduct $product,
            ModelsPriceRule $pricerule
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
        $this->currency = $currency;
        $this->pricerule = $pricerule;
    }

    /**
     * Displays the order overview page
     * @param integer $order_id
     */
    public function viewOrder($order_id)
    {
        $order = $this->getOrder($order_id);
        $this->order->setViewed($order);

        $this->setData('order', $order);

        $this->setDataSummaryOrder($order);
        $this->setDataCustomerOrder($order);
        $this->setDataComponentsOrder($order);
        $this->setDataShippingAddressOrder($order);

        $this->setTitleViewOrder($order);
        $this->setBreadcrumbViewOrder();
        $this->outputViewOrder();
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
     * @param array $order
     */
    protected function setTitleViewOrder(array $order)
    {
        $this->setTitle($this->text('Order #@order_id', array(
                    '@order_id' => $order['order_id'])));
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
     * @return array|void
     */
    protected function getOrder($order_id)
    {
        if (!is_numeric($order_id)) {
            return array();
        }

        $order = $this->order->get($order_id);

        if (!empty($order)) {
            return $this->prepareOrder($order);
        }

        return $this->outputError(404);
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

        $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        $order['customer'] = $this->text('Anonymous');
        $order['creator_formatted'] = $this->text('Customer');

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
     * @param array $order
     */
    protected function setDataSummaryOrder(array $order)
    {
        $data = array(
            'order' => $order,
            'statuses' => $this->order->getStatuses(),
        );

        $html = $this->render('sale/order/panes/summary', $data);
        $this->setData('pane_summary', $html);
    }

    /**
     * Sets customer pane on the order overview page
     * @param array $order
     */
    protected function setDataCustomerOrder(array $order)
    {
        $user_id = $order['user_id'];

        $user = null;
        if (is_numeric($user_id)) {
            $user = $this->user->get($user_id);
        }

        $data = array(
            'user' => $user,
            'order' => $order,
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
        $options = array('count' => true, 'user_id' => $user_id);
        return (int) $this->order->getList($options);
    }

    /**
     * Sets order components pane on the order overview page
     * @param array $order
     */
    protected function setDataComponentsOrder(array $order)
    {
        $components = $this->getComponentsOrder($order);

        $html = $this->render('sale/order/panes/components', array('components' => $components));
        $this->setData('pane_components', $html);
    }

    /**
     * Returns an array of prepared order components
     * @param array $order
     * @return array
     */
    protected function getComponentsOrder(array $order)
    {

        if (empty($order['data']['components'])) {
            return array();
        }

        $cart = $this->cart->getList(array('order_id' => $order['order_id']));

        $components = array();
        foreach ($order['data']['components'] as $type => $component) {

            if ($type === 'cart') {
                $components[$type] = $this->renderComponentCartOrder($component, $cart, $order);
                continue;
            }

            if (in_array($type, array('shipping', 'payment'))) {
                $components[$type] = $this->renderComponentMethodOrder($type, $component, $cart, $order);
                continue;
            }

            if (is_numeric($type)) {
                $components["rule_$type"] = $this->renderComponentRuleOrder($type, $component);
            }
        }

        ksort($components);
        return $components;
    }

    /**
     * Returns rendered cart component
     * @param array $component
     * @param array $cart
     * @param array $order
     * @return string
     */
    protected function renderComponentCartOrder(array $component, array $cart,
            array $order)
    {
        $products = array();
        foreach ($component as $cart_id => $price) {

            if (!isset($cart[$cart_id]['sku'])) {
                continue;
            }

            $product = $this->product->getBySku($cart[$cart_id]['sku'], $order['store_id']);
            $product['cart'] = $cart[$cart_id];
            $product['cart']['price_formatted'] = $this->price->format($price, $order['currency']);
            $products[] = $product;
        }

        return $this->render('sale/order/panes/components/cart', array('products' => $products));
    }

    /**
     * Returns rendered service component
     * @param string $type
     * @param integer $component
     * @param array $cart
     * @param array $order
     * @return string
     */
    protected function renderComponentMethodOrder($type, $component,
            array $cart, array $order)
    {
        $service = $this->order->getService($order[$type], $type, $cart, $order);
        $service['name'] = isset($service['name']) ? $service['name'] : $this->text('Unknown');
        $service['cart']['price_formatted'] = $this->price->format($component, $order['currency']);
        $service['cart']['type'] = ($type === 'payment') ? $this->text('Payment') : $this->text('Shipping');

        return $this->render('sale/order/panes/components/service', array('service' => $service));
    }

    /**
     * Returns rendered price rule component
     * @param integer $rule_id
     * @param integer $price
     * @return string
     */
    protected function renderComponentRuleOrder($rule_id, $price)
    {
        $rule = $this->pricerule->get($rule_id);

        $data = array('rule' => $rule, 'price' => $price);
        return $this->render('sale/order/panes/components/rule', $data);
    }

    /**
     * Returns rendered shipping address pane
     * @param array $order
     */
    protected function setDataShippingAddressOrder(array $order)
    {
        $address = $this->address->get($order['shipping_address']);
        $translated = $this->address->getTranslated($address);
        $geocode = $this->address->getGeocodeQuery($translated);

        $this->setJsSettings('map', array('address' => $geocode));

        $data = array(
            'order' => $order,
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
        $stores = $this->store->getNames();
        $statuses = $this->order->getStatuses();
        $currencies = $this->currency->getList();

        $this->setData('stores', $stores);
        $this->setData('statuses', $statuses);
        $this->setData('currencies', $currencies);

        $query = $this->getFilterQuery();
        $total = $this->getTotalOrder($query);
        $limit = $this->setPager($total, $query);
        $orders = $this->getListOrder($limit, $query);

        $allowed = array('store_id', 'order_id', 'status', 'created',
            'creator', 'user_id', 'total', 'currency');

        $this->setFilter($allowed, $query);
        $this->setData('orders', $orders);

        $this->setTitleListOrder();
        $this->setBreadcrumbListOrder();
        $this->outputListOrder();
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
        $orders = $this->order->getList($query);
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
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
            $order['is_new'] = $this->order->isNew($order);
        }

        return $orders;
    }

}
