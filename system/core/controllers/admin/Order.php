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
use core\models\Order as O;
use core\models\Country;
use core\models\State;
use core\models\Address;
use core\models\Price;
use core\models\Currency;
use core\models\Shipping;
use core\models\Payment;
use core\models\Product;
use core\models\Cart;
use core\models\PriceRule;

class Order extends Controller
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
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Payment model instance
     * @var \core\models\Shipping $payment
     */
    protected $payment;

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
     * @param O $order
     * @param Country $country
     * @param State $state
     * @param Address $address
     * @param Price $price
     * @param Currency $currency
     * @param Shipping $shipping
     * @param Payment $payment
     * @param Cart $cart
     * @param Product $product
     */
    public function __construct(O $order, Country $country, State $state, Address $address, Price $price, Currency $currency, Shipping $shipping, Payment $payment, Cart $cart, Product $product, PriceRule $pricerule)
    {
        parent::__construct();

        $this->cart = $cart;
        $this->state = $state;
        $this->order = $order;
        $this->price = $price;
        $this->payment = $payment;
        $this->product = $product;
        $this->country = $country;
        $this->address = $address;
        $this->currency = $currency;
        $this->shipping = $shipping;
        
        $this->pricerule = $pricerule;
    }

    /**
     * Displays the order overview page
     * @param integer $order_id
     */
    public function order($order_id)
    {
        $order = $this->get($order_id);
        $this->order->setViewed($order);

        $this->data['order'] = $order;
        $this->data['pane_summary'] = $this->renderPaneSummary($order);
        $this->data['pane_customer'] = $this->renderPaneCustomer($order);
        $this->data['pane_components'] = $this->renderPaneComponents($order);
        $this->data['pane_shipping_address'] = $this->renderPaneShippingAddress($order);

        $store = $this->store->get($order['store_id']);

        if (!empty($store['name'])) {
            $order['store_name'] = $store['name'];
        }

        //ddd($order);
        //ddd(unserialize(serialize($_SESSION['my_test'])));
        //$this->data['payment_method'] = $this->getPaymentMethod($order);
        //$this->data['shipping_method'] = $this->getShippingMethod($order);
        //$this->data['order_user'] = is_numeric($order['user_id']) ? $this->user->get($order['user_id']) : false;
        //$this->data['order_user_placed'] = $this->countOrders($order['user_id']);
        //ddd($this->data['order_user']);
        // ddd($order);
        //ddd($order);


        $this->setTitle($this->text('Order #@order_id', array('@order_id' => $order['order_id'])));

        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Orders'), 'url' => $this->url('admin/sale/order')));

        $this->output('sale/order/order');
    }

    /**
     * Returns rendered order summary pane
     * @param array $order
     * @return string
     */
    protected function renderPaneSummary(array $order)
    {
        $data = array(
            'order' => $order,
            'statuses' => $this->order->getStatuses(),
        );

        return $this->render('sale/order/panes/summary', $data);
    }

    /**
     * Returns rendered shipping address pane
     * @param array $order
     * @return string
     */
    protected function renderPaneShippingAddress(array $order)
    {
        $address = $this->getAddress($order['shipping_address']);
        $translated = $this->address->getTranslated($address);
        $geocode = $this->address->getGeocodeQuery($translated);

        $this->addJsSettings('map', array('address' => $geocode));

        $data = array(
            'order' => $order,
            'address' => $address,
            'items' => $this->address->getTranslated($address, true));

        return $this->render('sale/order/panes/shipping_address', $data);
    }

    /**
     * Returns rendered customer pane
     * @param array $order
     * @return string
     */
    protected function renderPaneCustomer(array $order)
    {
        $user_id = $order['user_id'];

        $user = null;
        if (is_numeric($user_id)) {
            $user = $this->user->get($user_id);
        }

        $data = array(
            'user' => $user,
            'order' => $order,
            'placed' => $this->getTotalPlacedOrders($user_id),
        );

        return $this->render('sale/order/panes/customer', $data);
    }

    /**
     * Returns rendered components pane
     * @param array $order
     * @return string
     */
    protected function renderPaneComponents(array $order)
    {
        if (empty($order['data']['components'])) {
            return array();
        }

        $cart = $this->order->getCart($order['order_id']);

        $components = array();
        foreach ($order['data']['components'] as $type => $component) {
            if ($type === 'cart') {
                $components[$type] = $this->renderComponentCart($type, $component, $cart, $order);
                continue;
            }

            if (in_array($type, array('shipping', 'payment'))) {
                $components[$type] = $this->renderComponentService($type, $component, $cart, $order);
                continue;
            }

            if (is_numeric($type)) {
                $components["rule_$type"] = $this->renderComponentRule($type, $component, $cart, $order);
            }
        }
        
        ksort($components);

        return $this->render('sale/order/panes/components', array('components' => $components));
    }

    /**
     * Returns rendered service component
     * @param string $type
     * @param integer $component
     * @param array $cart
     * @param array $order
     * @return string
     */
    protected function renderComponentService($type, $component, array $cart, array $order)
    {
        $service = $this->getService($order[$type], $type, $cart, $order);
        $service['name'] = isset($service['name']) ? $service['name'] : $this->text('Unknown');
        $service['cart']['price_formatted'] = $this->price->format($component, $order['currency']);
        $service['cart']['type'] = ($type === 'payment') ? $this->text('Payment') : $this->text('Shipping');

        return $this->render('sale/order/panes/components/service', array('service' => $service));
    }

    /**
     * Returns rendered cart component
     * @param string $type
     * @param array $component
     * @param array $cart
     * @param array $order
     * @return string
     */
    protected function renderComponentCart($type, array $component, array $cart, array $order)
    {
        $products = array();
        foreach ($component as $cart_id => $price) {
            if (isset($cart[$cart_id]['sku'])) {
                $product = $this->product->getBySku($cart[$cart_id]['sku'], $order['store_id']);
                $product['cart'] = $cart[$cart_id];
                $product['cart']['price_formatted'] = $this->price->format($price, $order['currency']);
                $products[] = $product;
            }
        }

        return $this->render('sale/order/panes/components/cart', array('products' => $products));
    }

    /**
     * Returns rendered price rule component
     * @param string $type
     * @param array $component
     * @param array $cart
     * @param array $order
     * @return string
     */
    protected function renderComponentRule($rule_id, $price, array $cart, array $order)
    {
        $rule = $this->pricerule->get($rule_id);

        return $this->render('sale/order/panes/components/rule', array('rule' => $rule, 'price' => $price));
    }

    /**
     * Returns a number of orders placed by a given user
     * @param integer|string $user_id
     * @return integer
     */
    protected function getTotalPlacedOrders($user_id)
    {
        return (int) $this->order->getList(array('count' => true, 'user_id' => $user_id));
    }

    /**
     * Returns an order
     * @param integer $order_id
     * @return array
     */
    protected function get($order_id)
    {
        if (!is_numeric($order_id)) {
            return array();
        }

        $order = $this->order->get($order_id);

        if (!empty($order)) {
            return $this->prepareOrder($order);
        }

        $this->outputError(404);
    }

    /**
     * Modifies order's array before rendering
     * @param array $order
     * @return array
     */
    protected function prepareOrder(array $order)
    {
        $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        $order['customer'] = $this->text('Anonymous');

        /**
          if (is_numeric($order['user_id'])) {
          $user = $this->user->get($order['user_id']);
          if (isset($user['user_id'])) {
          $order['customer'] = "{$user['name']} ({$user['email']})";
          }
          }

         */
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
     * Returns an address
     * @param integer $address_id
     * @return array
     */
    protected function getAddress($address_id)
    {
        $address = $this->address->get($address_id);

        return $address;
    }

    /**
     * Returns a service
     * @param string $id
     * @param string $type
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function getService($id, $type, array $cart, array $order)
    {
        return $this->{$type}->getService($id, $cart, $order);
    }

    /**
     * Displays the order admin overview page
     */
    public function orders()
    {
        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalOrders($query), $query);

        $this->data['orders'] = $this->getOrders($total, $query);

        $this->data['statuses'] = $this->order->getStatuses();
        $this->data['stores'] = $this->store->getNames();

        array_walk($this->data['orders'], function (&$order) {
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        });

        $this->data['currencies'] = $this->currency->getList();

        $sort_order = $this->request->get('order');

        foreach (array('store_id', 'status', 'created', 'creator', 'customer', 'total', 'currency') as $filter) {
            $this->data["filter_$filter"] = $this->request->get($filter);
            $this->data["sort_$filter"] = $this->url(false, array(
                'sort' => $filter,
                'order' => ($sort_order == 'desc') ? 'asc' : 'desc') + $query);
        }

        $this->setTitle($this->text('Orders'));
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->output('sale/order/list');
    }

    /**
     * Returns an array of orders
     * @param integer $total
     * @param array $query
     * @return integer
     */
    protected function getOrders($limit, $query)
    {
        $orders = $this->order->getList(array('limit' => $limit) + $query);

        return $this->prepareOrders($orders);
    }

    protected function prepareOrders($orders)
    {
        foreach ($orders as &$order) {
            $order['is_new'] = $this->order->isNew($order);
        }

        return $orders;
    }

    /**
     * Returns total number of orders for pages
     * @param array $query
     * @return integer
     */
    protected function getTotalOrders($query)
    {
        return $this->order->getList(array('count' => true) + $query);
    }
}
