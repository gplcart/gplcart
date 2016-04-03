<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\Logger;
use core\models\Cart;
use core\models\User;
use core\models\Price;
use core\classes\Cache;
use core\models\Payment;
use core\models\Product;
use core\classes\Request;
use core\models\Language;
use core\models\Shipping;
use core\models\PriceRule;
use core\models\Notification;
use core\exceptions\SystemLogical;

/**
 * Manages basic behaviors and data of store orders
 */
class Order
{

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Cart model instance
     * @var \core\models\Cart $cart
     */
    protected $cart;

    /**
     * Notification model instance
     * @var \core\models\Notification $notification
     */
    protected $notification;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Hook class instance
     * @var core\Hook $hook
     */
    protected $hook;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Payment model instance
     * @var \core\models\Payment $payment
     */
    protected $payment;

    /**
     * Constructor
     * @param User $user
     * @param Price $price
     * @param PriceRule $pricerule
     * @param Product $product
     * @param Cart $cart
     * @param Language $language
     * @param Notification $notification
     * @param Shipping $shipping
     * @param Payment $payment
     * @param Hook $hook
     * @param Logger $logger
     * @param Request $request
     * @param Config $config
     */
    public function __construct(User $user, Price $price, PriceRule $pricerule,
                                Product $product, Cart $cart,
                                Language $language, Notification $notification,
                                Shipping $shipping, Payment $payment,
                                Hook $hook, Logger $logger, Request $request,
                                Config $config)
    {
        $this->hook = $hook;
        $this->user = $user;
        $this->cart = $cart;
        $this->price = $price;
        $this->logger = $logger;
        $this->config = $config;
        $this->product = $product;
        $this->payment = $payment;
        $this->request = $request;
        $this->language = $language;
        $this->shipping = $shipping;
        $this->pricerule = $pricerule;
        $this->db = $this->config->db();
        $this->notification = $notification;
    }

    /**
     * Returns an array of order statuses
     * @return array
     */
    public function getStatuses()
    {
        $statuses = &Cache::memory('statuses');

        if (isset($statuses)) {
            return $statuses;
        }

        $statuses = $this->getDefaultStatuses();

        $this->hook->fire('order.statuses', $statuses);
        return $statuses;
    }

    /**
     * Returns status name
     * @param string $id
     * @return string
     */
    public function getStatusName($id)
    {
        $statuses = $this->getStatuses();
        return isset($statuses[$id]) ? $statuses[$id] : '';
    }

    /**
     * Returns an array of orders or total number of orders
     * @param array $data
     * @return mixed
     */
    public function getList($data = array())
    {
        $sql = 'SELECT o.*, u.email AS creator, uc.name AS customer_name, uc.email AS customer_email,
        CONCAT(uc.name, "", uc.email) AS customer, h.time AS viewed, a.country,
        a.city_id, a.address_1, a.address_2, a.phone, a.postcode, a.first_name, a.middle_name, a.last_name';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(DISTINCT o.order_id)';
        }

        $sql .= '
        FROM orders o
        LEFT JOIN user u ON(o.creator=u.user_id)
        LEFT JOIN user uc ON(o.user_id=uc.user_id)
        LEFT JOIN address a ON(o.shipping_address=a.address_id)
        LEFT JOIN history h ON(h.user_id=? AND h.id_key=? AND h.id_value=o.order_id)
        WHERE o.order_id IS NOT NULL';

        $where = array($this->user->id(), 'order_id');

        if (isset($data['store_id'])) {
            $sql .= ' AND o.store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['total'])) {
            $sql .= ' AND o.total = ?';
            $where[] = (int) $data['total'];
        }

        if (isset($data['currency'])) {
            $sql .= ' AND o.currency = ?';
            $where[] = $data['currency'];
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND o.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND o.status = ?';
            $where[] = $data['status'];
        }

        if (isset($data['creator'])) {
            $sql .= ' AND u.email LIKE ?';
            $where[] = "%{$data['creator']}%";
        }

        if (isset($data['customer'])) {
            $sql .= ' AND (uc.email LIKE ? OR uc.name LIKE ?)';
            $where[] = "%{$data['customer']}%";
            $where[] = "%{$data['customer']}%";
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            switch ($data['sort']) {
                case 'order_id':
                    $field = 'o.order_id';
                    break;
                case 'store_id':
                    $field = 'o.store_id';
                    break;
                case 'status':
                    $field = 'o.status';
                    break;
                case 'created':
                    $field = 'o.created';
                    break;
                case 'total':
                    $field = 'o.total';
                    break;
                case 'currency':
                    $field = 'o.currency';
                    break;
                case 'customer':
                    $field = 'customer';
                    break;
                case 'creator':
                    $field = 'u.email';
            }

            if (isset($field)) {
                $sql .= " ORDER BY $field {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY o.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $order) {
            $order = $this->prepareOrder($order);
            $list[$order['order_id']] = $order;
        }

        $this->hook->fire('order.list', $list);

        return $list;
    }

    /**
     * Loads an order from the database
     * @param integer $order_id
     * @return array
     */
    public function get($order_id)
    {
        $this->hook->fire('get.order.before', $order_id);

        $sql = 'SELECT o.*, u.name AS user_name, u.email AS user_email
                FROM orders o
                LEFT JOIN user u ON(o.user_id=u.user_id)
                WHERE o.order_id=:order_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':order_id' => (int) $order_id));

        $order = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($order['data'])) {
            $order = $this->prepareOrder($order);
        }

        $this->hook->fire('get.order.after', $order_id, $order);
        return $order;
    }

    /**
     * Returns an order component
     * @param string $component
     * @param array $order
     * @param mixed $default
     * @return mixed
     */
    public function getComponent($component, $order, $default = null)
    {
        if (isset($order['data']['components'][$component])) {
            return $order['data']['components'][$component];
        }

        return $default;
    }

    /**
     * Returns an array of cart items for the given order ID
     * @param integer $order_id
     * @return array
     */
    public function getCart($order_id)
    {
        return $this->cart->getList(array('order_id' => (int) $order_id));
    }

    /**
     * Updates an order
     * @param integer $order_id
     * @param array $data
     * @return boolean
     */
    public function update($order_id, $data)
    {
        $this->hook->fire('update.order.before', $order_id, $data);

        if (empty($order_id)) {
            return false;
        }

        $values = array(
            'modified' => isset($data['modified']) ? (int) $data['modified'] : GC_TIME
        );

        if (isset($data['created'])) {
            $values['created'] = (int) $data['created'];
        }

        if (!empty($data['user_id'])) {
            $values['user_id'] = $data['user_id'];
        }

        if (!empty($data['creator'])) {
            $values['creator'] = (int) $data['creator'];
        }

        if (!empty($data['comment'])) {
            $values['comment'] = $data['comment'];
        }

        if (!empty($data['status'])) {
            $values['status'] = $data['status'];
        }

        if (isset($data['store_id'])) {
            $values['store_id'] = (int) $data['store_id'];
        }

        if (!empty($data['currency'])) {
            $values['currency'] = $data['currency'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['total'])) {
            $values['total'] = (int) $data['total'];
        }

        if (!empty($data['shipping_address'])) {
            $values['shipping_address'] = (int) $data['shipping_address'];
        }

        if (!empty($data['payment_address'])) {
            $values['payment_address'] = (int) $data['payment_address'];
        }

        if (!empty($data['shipping'])) {
            $values['shipping'] = $data['shipping'];
        }

        if (!empty($data['payment'])) {
            $values['payment'] = $data['payment'];
        }

        if (empty($values)) {
            return false;
        }

        $result = $this->db->update('orders', $values, array('order_id' => (int) $order_id));
        $this->hook->fire('update.order.after', $order_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes an order
     * @param integer $order_id
     * @return boolean
     */
    public function delete($order_id)
    {
        $this->hook->fire('delete.order.before', $order_id);

        if (empty($order_id)) {
            return false;
        }

        $this->db->delete('orders', array('order_id' => (int) $order_id));
        $this->db->delete('cart', array('order_id' => (int) $order_id));
        $this->db->delete('history', array('id_key' => 'order_id', 'id_value' => (int) $order_id));

        $this->hook->fire('delete.order.after', $order_id);
        return true;
    }

    /**
     * Mark the order ID is viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function setViewed($order)
    {
        $user_id = $this->user->id();

        if ($this->isViewed($order, $user_id)) {
            return true; // Record already exists
        }

        $lifespan = (int) $this->config->get('history_lifespan', 2628000);

        // Do not mark again old orders.
        // Their history records probably have been removed by cron
        if ((GC_TIME - $order['created']) >= $lifespan) {
            return true;
        }

        $values = array(
            'time' => GC_TIME,
            'user_id' => $user_id,
            'id_key' => 'order_id',
            'id_value' => (int) $order['order_id']
        );

        return (bool) $this->db->insert('history', $values);
    }

    /**
     * Wheter the order ID is already in the history table
     * @param array $order
     * @param integer $user_id
     * @return boolean
     */
    public function isViewed($order, $user_id)
    {
        $sql = 'SELECT history_id
                FROM history
                WHERE id_key=:id_key
                    AND id_value=:id_value
                    AND user_id=:user_id';

        $where = array(
            ':id_key' => 'order_id',
            ':id_value' => (int) $order['order_id'],
            ':user_id' => (int) $user_id
        );

        $sth = $this->db->prepare($sql);
        $sth->execute($where);
        return (bool) $sth->fetchColumn();
    }

    /**
     * Whether the order has been already viewed by the user
     * @param array $order
     * @return boolean
     */
    public function isNew($order)
    {
        $viewed = isset($order['viewed']) ? (int) $order['viewed'] : 0;
        $lifespan = (int) $this->config->get('history_lifespan', 2628000);

        if (empty($viewed)) {
            return !((GC_TIME - (int) $order['created']) > $lifespan);
        }

        return ((GC_TIME - $viewed) > $lifespan);
    }

    /**
     * Submits an order
     * @param array $data
     * @param array $cart
     * @return array|boolean
     */
    public function submit($data, $cart)
    {
        $this->hook->fire('submit.order.before', $data, $cart);

        if (empty($data)) {
            return false; // Blocked by a module
        }
        
        $this->setComponents($data, $cart);
        $order_id = $this->add($data);

        if (empty($order_id)) {
            return false; // Blocked by a module
        }

        // Get fresh order from the database
        $order = $this->get($order_id);

        $this->logSubmit($order);
        $this->setPriceRule($order);
        $this->setCart($order, $cart);
        $this->setNotification($order);

        $result = array(
            'order' => $order,
            'redirect' => "checkout/complete/$order_id");

        $this->hook->fire('submit.order.after', $order, $cart, $result);
        return $result;
    }

    /**
     * Sets user notifications
     * @param array $order
     * @return mixed
     */
    public function setNotification($order)
    {
        $this->notification->set('order_created_admin', array($order));

        if (is_numeric($order['user_id']) && !empty($order['user_email'])) {
            return $this->notification->set('order_created_customer', array($order));
        }
    }

    /**
     * Returns a default order status
     * @return string
     */
    public function getDefaultStatus()
    {
        return $this->config->get('order_status', 'pending');
    }

    /**
     * Returns a default message to be shown on the order complete page
     * @param array $order
     * @return string
     */
    public function getCompleteMessage($order)
    {
        if (is_numeric($order['user_id'])) {
            return $this->notification->set('order_complete_customer', array($order));
        }

        return $this->notification->set('order_complete_anonymous', array($order));
    }

    /**
     * Adds an order
     * @param array $order
     * @return boolean|integer
     */
    public function add($order)
    {
        $this->hook->fire('add.order.before', $order);

        if (empty($order)) {
            return false;
        }

        if (empty($order['data']['user'])) {
            $order['data']['user'] = $this->getUserData();
        }

        $values = array(
            'modified' => 0,
            'user_id' => $order['user_id'],
            'store_id' => isset($order['store_id']) ? (int) $order['store_id'] : $this->config->get('store', 1),
            'status' => isset($order['status']) ? $order['status'] : $this->getDefaultStatus(),
            'created' => empty($order['created']) ? GC_TIME : (int) $order['created'],
            'total' => empty($order['total']) ? 0 : (int) $order['total'],
            'currency' => empty($order['currency']) ? '' : $order['currency'],
            'data' => serialize($order['data']),
            'creator' => empty($order['creator']) ? 0 : (int) $order['creator'],
            'shipping_address' => empty($order['shipping_address']) ? 0 : (int) $order['shipping_address'],
            'payment_address' => empty($order['payment_address']) ? 0 : (int) $order['payment_address'],
            'shipping' => empty($order['shipping']) ? '' : $order['shipping'],
            'payment' => empty($order['payment']) ? '' : $order['payment'],
            'comment' => empty($order['comment']) ? '' : $order['comment']
        );

        $order_id = $this->db->insert('orders', $values);
        $this->hook->fire('add.order.after', $values, $order_id);
        return $order_id;
    }

    /**
     * Calculates order totals
     * @staticvar int $total
     * @param array $cart
     * @param array $data
     * @return array
     */
    public function calculate(array $cart, array $data)
    {
        static $total = 0;

        $order = $data['order'];
        $total += (int) $cart['total'];

        $components = array();
        foreach (array('shipping', 'payment') as $module) {
            if (isset($order[$module]) && isset($data[$module . '_services'][$order[$module]]['price'])) {
                $price = (int) $data[$module . '_services'][$order[$module]]['price'];
                $components[$module] = array('price' => $price);
                $total += $price;
            }
        }

        $this->pricerule->calculate($total, $cart, $data, $components);
        return array('total' => $total, 'currency' => $cart['currency'], 'components' => $components);
    }

    /**
     * Whether a given code matches the price rule
     * @param integer $price_rule_id
     * @param string $code
     * @return boolean
     */
    public function codeMatches($price_rule_id, $code)
    {
        return $this->pricerule->codeMatches($price_rule_id, $code);
    }

    /**
     * Returns an array of default order status
     * @return array
     */
    protected function getDefaultStatuses()
    {
        $default = array(
            'pending' => $this->language->text('Pending'),
            'processing' => $this->language->text('Processing'),
            'canceled' => $this->language->text('Canceled'),
            'dispatched' => $this->language->text('Dispatched'),
            'delivered' => $this->language->text('Delivered'),
            'completed' => $this->language->text('Completed')
        );

        return $default;
    }

    /**
     * Sets price rules after the order was created
     * @param array $order
     */
    protected function setPriceRule($order)
    {
        foreach (array_keys($order['data']['components']) as $component_id) {
            if (!is_numeric($component_id)) {
                continue; // We need only rules
            }

            $rule = $this->pricerule->get($component_id);

            // Mark the coupon was used
            if (isset($rule['type']) && $rule['type'] === 'order' && !empty($rule['code'])) {
                $this->pricerule->setUsed($rule['price_rule_id']);
            }
        }
    }

    /**
     * Set cart items after order was created
     * @param array $order
     * @param array $cart
     */
    protected function setCart($order, $cart)
    {
        foreach ($cart['items'] as $item) {
            $this->cart->update($item['cart_id'], array('order_id' => $order['order_id']));
        }
    }

    /**
     * Returns a service (e.g shipping service)
     * @param string $id
     * @param string $type
     * @param array $cart
     * @param array $order
     * @return array
     * @throws SystemLogical
     */
    public function getService($id, $type, array $cart, array $order)
    {
        if (in_array($type, array('shipping', 'payment'))) {
            return $this->{$type}->getService($id, $cart, $order);
        }

        return array();
    }

    /**
     * Returns services by a type
     * @param string $type
     * @param array $cart
     * @param array $order
     * @return array
     */
    public function getServices($type, array $cart, array $order)
    {
        if (in_array($type, array('shipping', 'payment'))) {
            return $this->{$type}->getServices($cart, $order);
        }

        return array();
    }

    /**
     * Logs the order submit event
     * @param array $order
     */
    protected function logSubmit($order)
    {
        $log = array(
            'message' => 'User %s has submitted order',
            'variables' => array('%s' => $order['user_id'])
        );

        $this->logger->log('checkout', $log);
    }

    /**
     *
     * @return type
     */
    protected function getUserData()
    {
        return array(
            'ip' => $this->request->ip(),
            'agent' => $this->request->agent()
        );
    }
    
    /**
     * Prepares order components
     * @param array $order
     * @param array $cart
     * @return array
     */
    protected function setComponents(&$order, $cart)
    {
        foreach ($cart['items'] as $cart_id => $item) {
            $order['data']['components']['cart'][$cart_id] = $item['total'];
        }

        return $order;
    }

    /**
     * Returns an array of order components
     * @param array $order
     * @return array
     */
    public function getComponents(array $order)
    {
        $cart = $this->getCart($order['order_id']);

        $prepared = array();
        foreach ($order['data']['components'] as $name => $component) {
            if ($name === 'cart') {
                $prepared[$name] = $this->prepareComponentCart($component, $cart, $order);
                continue;
            }

            if (in_array($name, array('shipping', 'payment'))) {
                $prepared['service'][$name] = $this->prepareComponentService($name, $component, $cart, $order);
                continue;
            }

            if (is_numeric($name)) {
                $prepared['rule'][$name] = $this->prepareComponentPriceRule($name, $component, $cart, $order);
            }
        }

        ksort($prepared);
        return $prepared;
    }

    /**
     * Prepares cart order component
     * @param array $component
     * @param array $cart
     * @param array  $order
     * @return array
     */
    protected function prepareComponentCart(array $component, $cart, $order)
    {
        foreach ($component as $cart_id => $price) {
            if (isset($cart[$cart_id]['sku'])) {
                $cart[$cart_id]['component_price'] = $price;
                $cart[$cart_id]['component_price_formatted'] = $this->price->format($price, $order['currency']);
                $cart[$cart_id]['product'] = $this->product->getBySku($cart[$cart_id]['sku'], $order['store_id']);
            }
        }

        return $cart;
    }

    /**
     * Prepares service cart component
     * @param string $type
     * @param integer $price
     * @param array $cart
     * @param array $order
     */
    protected function prepareComponentService($type, $price, $cart, $order)
    {
        $service = $this->getService($order[$type], $type, $cart, $order);
        $service['component_price'] = $price;
        $service['component_price_formatted'] = $this->price->format($price, $order['currency']);
        return $service;
    }

    /**
     * Prepares price rule order component
     * @param integer $rule_id
     * @param integer $price
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function prepareComponentPriceRule($rule_id, $price, $cart, $order)
    {
        $rule = $this->pricerule->get($rule_id);
        $rule['component_price'] = $price;
        $rule['component_price_formatted'] = $this->price->format($price, $order['currency']);
        return $rule;
    }
    
    /**
     * Returns a prepared order
     * @param array $order
     * @return array
     */
    protected function prepareOrder($order)
    {
        $order['data'] = unserialize($order['data']);
        $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        $order['status_formatted'] = $this->getStatusName($order['status']);
        return $order;
    }
}
