<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\models\Mail as MailModel;
use gplcart\core\models\Cart as CartModel;
use gplcart\core\models\User as UserModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Language as LanguageModel;
use gplcart\core\models\PriceRule as PriceRuleModel;

/**
 * Manages basic behaviors and data related to store orders
 */
class Order extends Model
{

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

    /**
     * Mail model instance
     * @var \gplcart\core\models\Mail $mail
     */
    protected $mail;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param UserModel $user
     * @param PriceModel $price
     * @param PriceRuleModel $pricerule
     * @param ProductModel $product
     * @param CartModel $cart
     * @param LanguageModel $language
     * @param MailModel $mail
     * @param RequestHelper $request
     */
    public function __construct(UserModel $user, PriceModel $price,
            PriceRuleModel $pricerule, ProductModel $product, CartModel $cart,
            LanguageModel $language, MailModel $mail, RequestHelper $request)
    {
        parent::__construct();

        $this->mail = $mail;
        $this->user = $user;
        $this->cart = $cart;
        $this->price = $price;
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

        $where = array((int) $this->user->getSession('user_id'), 'order_id');

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

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
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

        $data['modified'] = GC_TIME;

        if (!empty($data['cart'])) {
            $this->prepareComponents($data, $data['cart']);
        }

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
            $this->db->delete('order_log', $conditions);
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
        $user_id = (int) $this->user->getSession('user_id');

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
     * @param array $options
     * @return array
     */
    public function submit(array $data, array $cart, array $options = array())
    {
        $this->hook->fire('submit.order.before', $data, $cart, $options);

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('An error occurred')
        );

        if (empty($data)) {
            return $result; // Blocked by a module
        }

        $this->prepareComponents($data, $cart);
        $order_id = $this->add($data);

        if (empty($order_id)) {
            return $result; // Blocked by a module
        }

        // Get fresh order from the database
        $order = $this->get((int) $order_id);

        $this->setPriceRule($order);
        $this->updateCart($order, $cart);

        $result = array(
            'order' => $order,
            'severity' => 'success',
            'redirect' => "admin/sale/order/$order_id",
            'message' => $this->language->text('Order has been created')
        );

        if (empty($options['admin'])) {

            $this->setNotification($order);

            $result = array(
                'message' => '',
                'order' => $order,
                'severity' => 'success',
                'redirect' => "checkout/complete/$order_id"
            );
        }

        $this->hook->fire('submit.order.after', $order, $cart, $options, $result);
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
        $default = $this->getDefaultStatus();
        return $this->config->get('order_status_initial', $default);
    }

    /**
     * Returns a default message to be shown on the order complete page
     * @param array $order
     * @return string
     */
    public function getCompleteMessage(array $order)
    {
        if (is_numeric($order['user_id'])) {
            $message = $this->getCompleteMessageLoggedIn($order);
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
    protected function getCompleteMessageLoggedIn(array $order)
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

        $order['created'] = GC_TIME;
        $order += array('status' => $this->getDefaultStatus());

        if (empty($order['data']['user'])) {
            $order['data']['user'] = $this->getUserData();
        }

        $order['order_id'] = $this->db->insert('orders', $order);
        $this->hook->fire('add.order.after', $order);

        return $order['order_id'];
    }

    /**
     * Adds an order log record to the database
     * @param string $message
     * @param integer $user_id
     * @param array|integer $order
     * @return integer
     */
    public function addLog($message, $user_id, $order)
    {
        if (is_numeric($order)) {
            $order = $this->get($order);
        }

        $values = array(
            'data' => $order,
            'text' => $message,
            'created' => GC_TIME,
            'user_id' => $user_id,
            'order_id' => $order['order_id']
        );

        return $this->db->insert('order_log', $values);
    }

    /**
     * Returns an array of log records
     * @param array $data
     * @return array
     */
    public function getLogList(array $data)
    {
        $sql = 'SELECT ol.*, u.name AS user_name, u.email AS user_email, u.status AS user_status';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(ol.order_log_id)';
        }

        $sql .= ' FROM order_log ol'
                . ' LEFT JOIN user u ON(ol.user_id=u.user_id)'
                . ' WHERE ol.order_id=?';

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, array($data['order_id']));
        }

        $sql .= ' ORDER BY ol.created DESC';

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $params = array('index' => 'order_log_id', 'unserialize' => 'data');
        return $this->db->fetchAll($sql, array($data['order_id']), $params);
    }

    /**
     * Returns an order log
     * @param integer $order_log_id
     * @return array
     */
    public function getLog($order_log_id)
    {
        $sql = 'SELECT ol.*, u.name AS user_name, u.email AS user_email, u.status AS user_status'
                . ' FROM order_log ol'
                . ' LEFT JOIN user u ON(ol.user_id=u.user_id)'
                . ' WHERE ol.order_log_id=?';

        $params = array('unserialize' => 'data');
        return $this->db->fetch($sql, array($order_log_id), $params);
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

        return array(
            'total' => $total,
            'components' => $components,
            'currency' => $cart['currency']
        );
    }

    /**
     * Whether a given code matches the price rule
     * @param integer $price_rule_id
     * @param string $code
     * @return boolean
     */
    public function priceRuleCodeMatches($price_rule_id, $code)
    {
        return $this->pricerule->codeMatches($price_rule_id, $code);
    }

    /**
     * Returns an array of default order status
     * @return array
     */
    protected function getDefaultStatuses()
    {
        $statuses = array(
            'pending' => $this->language->text('Pending'),
            'canceled' => $this->language->text('Canceled'),
            'delivered' => $this->language->text('Delivered'),
            'completed' => $this->language->text('Completed'),
            'processing' => $this->language->text('Processing'),
            'dispatched' => $this->language->text('Dispatched')
        );

        return $statuses;
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
            if ($rule['code'] !== '') {
                $this->pricerule->setUsed($rule['price_rule_id']);
            }
        }
    }

    /**
     * Update cart items after order was created
     * @param array $order
     * @param array $cart
     */
    protected function updateCart(array $order, array $cart)
    {
        foreach ($cart['items'] as $item) {

            $values = array(
                // Replace default order ID (0) with order ID we just created
                'order_id' => $order['order_id'],
                // Make sure that cart items have the same user ID with order.
                // It can be different e.g admin created the order using own cart
                'user_id' => $order['user_id']
            );

            $this->cart->update($item['cart_id'], $values);
        }
    }

    /**
     * Returns the current user data to be used in order logs
     * @return array
     */
    protected function getUserData()
    {
        $data = array(
            'ip' => $this->request->ip(),
            'agent' => $this->request->agent()
        );

        return $data;
    }

    /**
     * Prepares order components
     * @param array $order
     * @param array $cart
     * @return null
     */
    protected function prepareComponents(array &$order, array $cart)
    {
        if (empty($cart['items'])) {
            return null;
        }

        foreach ($cart['items'] as $sku => $item) {
            $order['data']['components']['cart'][$sku] = $item['total'];
        }
    }

}
