<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Logger;
use core\classes\Cache;
use core\classes\Request;
use core\models\Mail as ModelsMail;
use core\models\Cart as ModelsCart;
use core\models\User as ModelsUser;
use core\models\Price as ModelsPrice;
use core\models\Product as ModelsProduct;
use core\models\Language as ModelsLanguage;
use core\models\PriceRule as ModelsPriceRule;

/**
 * Manages basic behaviors and data related to store orders
 */
class Order extends Model
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
     * Mail model instance
     * @var \core\models\Mail $mail
     */
    protected $mail;

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
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param ModelsUser $user
     * @param ModelsPrice $price
     * @param ModelsPriceRule $pricerule
     * @param ModelsProduct $product
     * @param ModelsCart $cart
     * @param ModelsLanguage $language
     * @param ModelsMail $mail
     * @param Request $request
     * @param Logger $logger
     */
    public function __construct(ModelsUser $user, ModelsPrice $price,
            ModelsPriceRule $pricerule, ModelsProduct $product,
            ModelsCart $cart, ModelsLanguage $language, ModelsMail $mail, Request $request,
            Logger $logger)
    {
        parent::__construct();

        $this->mail = $mail;
        $this->user = $user;
        $this->cart = $cart;
        $this->price = $price;
        $this->logger = $logger;
        $this->product = $product;
        $this->request = $request;
        $this->language = $language;
        $this->pricerule = $pricerule;
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
     * Returns a status name
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
     * @return array|integer
     */
    public function getList($data = array())
    {
        $sql = 'SELECT o.*, u.email AS creator,'
                . 'uc.name AS customer_name, uc.email AS customer_email,'
                . 'CONCAT(uc.name, "", uc.email) AS customer,'
                . 'h.time AS viewed, a.country, a.city_id, a.address_1,'
                . 'a.address_2, a.phone, a.postcode, a.first_name,'
                . 'a.middle_name, a.last_name';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(DISTINCT o.order_id)';
        }

        $sql .= ' FROM orders o'
                . ' LEFT JOIN user u ON(o.creator=u.user_id)'
                . ' LEFT JOIN user uc ON(o.user_id=uc.user_id)'
                . ' LEFT JOIN address a ON(o.shipping_address=a.address_id)'
                . ' LEFT JOIN history h ON(h.user_id=? AND h.id_key=? AND h.id_value=o.order_id)'
                . ' WHERE o.order_id IS NOT NULL';

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

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'order_id' => 'o.order_id',
            'store_id' => 'o.store_id',
            'status' => 'o.status',
            'created' => 'o.created',
            'total' => 'o.total',
            'currency' => 'o.currency',
            'customer' => 'customer',
            'creator' => 'u.email'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']]) && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY o.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('unserialize' => 'data', 'index' => 'order_id');
        $orders = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('order.list', $orders);
        return $orders;
    }

    /**
     * Loads an order from the database
     * @param integer $order_id
     * @return array
     */
    public function get($order_id)
    {
        $this->hook->fire('get.order.before', $order_id);

        $sql = 'SELECT o.*, u.name AS user_name, u.email AS user_email'
                . ' FROM orders o'
                . ' LEFT JOIN user u ON(o.user_id=u.user_id)'
                . ' WHERE o.order_id=?';

        $order = $this->db->fetch($sql, array($order_id), array('unserialize' => 'data'));
        
        $this->attachCart($order);

        $this->hook->fire('get.order.after', $order_id, $order);
        return $order;
    }
    
    /**
     * Sets cart items to the order
     * @param array $order
     */
    protected function attachCart(array &$order)
    {
        if (isset($order['order_id'])) {
            $order['cart'] = $this->cart->getList(array('order_id' => $order['order_id']));
        }
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
     * Updates an order
     * @param integer $order_id
     * @param array $data
     * @return boolean
     */
    public function update($order_id, array $data)
    {
        $this->hook->fire('update.order.before', $order_id, $data);

        if (empty($order_id)) {
            return false;
        }

        $data += array('modified' => GC_TIME);

        $result = $this->db->update('orders', $data, array('order_id' => $order_id));
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

        $conditions = array('order_id' => $order_id);
        $conditions2 = array('id_key' => 'order_id', 'id_value' => $order_id);

        $deleted = (bool) $this->db->delete('orders', $conditions);

        if ($deleted) {
            $this->db->delete('cart', $conditions);
            $this->db->delete('history', $conditions2);
        }

        $this->hook->fire('delete.order.after', $order_id, $deleted);
        return (bool) $deleted;
    }

    /**
     * Mark the order ID is viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function setViewed(array $order)
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
            'id_value' => $order['order_id']
        );

        return (bool) $this->db->insert('history', $values);
    }

    /**
     * Wheter the order ID is already in the history table
     * @param array $order
     * @param integer $user_id
     * @return boolean
     */
    public function isViewed(array $order, $user_id)
    {
        $sql = 'SELECT history_id'
                . ' FROM history'
                . ' WHERE id_key=?'
                . ' AND id_value=?'
                . ' AND user_id=?';

        $conditions = array(
            'order_id',
            $order['order_id'],
            $user_id
        );

        return (bool) $this->db->fetchColumn($sql, $conditions);
    }

    /**
     * Whether the order has been already viewed by the user
     * @param array $order
     * @return boolean
     */
    public function isNew(array $order)
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
     * @return array
     */
    public function submit(array $data, array $cart)
    {
        $this->hook->fire('submit.order.before', $data, $cart);

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('An error occurred')
        );

        if (empty($data)) {
            return $result; // Blocked by a module
        }

        $this->setComponents($data, $cart);
        $order_id = $this->add($data);

        if (empty($order_id)) {
            return $result; // Blocked by a module
        }

        // Get fresh order from the database
        $order = $this->get($order_id);

        $this->logSubmit($order);
        $this->setPriceRule($order);
        $this->setCart($order, $cart);
        $this->setNotification($order);

        $result = array(
            'order' => $order,
            'message' => '',
            'severity' => 'success',
            'redirect' => "checkout/complete/$order_id"
        );

        $this->hook->fire('submit.order.after', $order, $cart, $result);
        return $result;
    }

    /**
     * Sets user notifications
     * @param array $order
     * @return mixed
     */
    public function setNotification(array $order)
    {
        $this->mail->set('order_created_admin', array($order));

        if (is_numeric($order['user_id']) && !empty($order['user_email'])) {
            return $this->mail->set('order_created_customer', array($order));
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
     * Returns an initial order status
     * @return string
     */
    public function getInitialStatus()
    {
        return $this->getDefaultStatus();
    }

    /**
     * Returns a default message to be shown on the order complete page
     * @param array $order
     * @return string
     */
    public function getCompleteMessage(array $order)
    {
        if (is_numeric($order['user_id'])) {
            $message = $this->getCompleteMessageLogged($order);
        } else {
            $message = $this->getCompleteMessageAnonymous($order);
        }
        
        $this->hook->fire('order.complete.message', $message, $order);
        return $message;
    }

    /**
     * Returns a message for a logged in user when checkout is completed
     * @param array $order
     * @return string
     */
    protected function getCompleteMessageLogged(array $order)
    {
        $default = 'Thank you for your order! Order ID: <a href="!url">!order_id</a>, status: !status';
        $message = $this->config->get('order_complete_message', $default);

        $variables = array(
            '!order_id' => $order['order_id'],
            '!url' => $this->request->base() . "account/{$order['user_id']}",
            '!status' => $this->getStatusName($order['status'])
        );

        return $this->language->text($message, $variables);
    }

    /**
     * Returns a message for an anonymous user when checkout is completed
     * @param array $order
     * @return string
     */
    protected function getCompleteMessageAnonymous(array $order)
    {
        $default = 'Thank you for your order! Order ID: !order_id, status: !status';
        $message = $this->config->get('order_complete_message_anonymous', $default);

        $variables = array(
            '!order_id' => $order['order_id'],
            '!status' => $this->getStatusName($order['status'])
        );

        return $this->language->text($message, $variables);
    }

    /**
     * Adds an order
     * @param array $order
     * @return boolean|integer
     */
    public function add(array $order)
    {
        $this->hook->fire('add.order.before', $order);

        if (empty($order)) {
            return false;
        }

        $order += array(
            'created' => GC_TIME, 'status' => $this->getDefaultStatus());

        if (empty($order['data']['user'])) {
            $order['data']['user'] = $this->getUserData();
        }

        $order['order_id'] = $this->db->insert('orders', $order);
        $this->hook->fire('add.order.after', $order);
        return $order['order_id'];
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

        $total += $cart['total'];

        $components = array();
        foreach (array('shipping', 'payment') as $module) {

            if (isset($data[$module]) && isset($data[$module . '_methods'][$data[$module]]['price'])) {
                $price = $data[$module . '_methods'][$data[$module]]['price'];
                $components[$module] = array('price' => $price);
                $total += $components[$module]['price'];
            }

            $this->hook->fire("calculate.order.$module", $total, $cart, $data, $components);
        }

        $this->pricerule->calculate($total, $cart, $data, $components);
        $this->hook->fire('calculate.order', $total, $cart, $data, $components);

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
        return array(
            'pending' => $this->language->text('Pending'),
            'processing' => $this->language->text('Processing'),
            'canceled' => $this->language->text('Canceled'),
            'dispatched' => $this->language->text('Dispatched'),
            'delivered' => $this->language->text('Delivered'),
            'completed' => $this->language->text('Completed')
        );
    }

    /**
     * Sets price rules after the order was created
     * @param array $order
     */
    protected function setPriceRule(array $order)
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
    protected function setCart(array $order, array $cart)
    {
        foreach ($cart['items'] as $item) {
            $this->cart->update($item['cart_id'], array('order_id' => $order['order_id']));
        }
    }

    /**
     * Logs the order submit event
     * @param array $order
     */
    protected function logSubmit(array $order)
    {
        $log = array(
            'message' => 'User %s has submitted order',
            'variables' => array('%s' => $order['user_id'])
        );

        $this->logger->log('checkout', $log);
    }

    /**
     * Returns the current user data to be used in order logs
     * @return array
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
    protected function setComponents(array &$order, array $cart)
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
        $cart = $this->cart->getList(array('order_id' => $order['order_id']));

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
    protected function prepareComponentCart(array $component, array $cart,
            array $order)
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
    protected function prepareComponentService($type, $price, array $cart,
            array $order)
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
    protected function prepareComponentPriceRule($rule_id, $price, array $cart,
            array $order)
    {
        $rule = $this->pricerule->get($rule_id);
        $rule['component_price'] = $price;
        $rule['component_price_formatted'] = $this->price->format($price, $order['currency']);
        return $rule;
    }

}
