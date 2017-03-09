<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
use gplcart\core\models\Mail as MailModel,
    gplcart\core\models\Cart as CartModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\helpers\Request as RequestHelper;

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
        $this->hook->fire('order.get.before', $order_id);

        $sql = 'SELECT o.*, u.name AS user_name, u.email AS user_email'
                . ' FROM orders o'
                . ' LEFT JOIN user u ON(o.user_id=u.user_id)'
                . ' WHERE o.order_id=?';

        $order = $this->db->fetch($sql, array($order_id), array('unserialize' => 'data'));

        $this->attachCart($order);

        $this->hook->fire('order.get.after', $order);
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
        $this->hook->fire('order.update.before', $order_id, $data);

        if (empty($order_id)) {
            return false;
        }

        $data['modified'] = GC_TIME;

        if (!empty($data['cart'])) {
            $this->prepareComponents($data, $data['cart']);
        }

        $result = $this->db->update('orders', $data, array('order_id' => $order_id));
        $this->hook->fire('order.update.after', $order_id, $data, $result);

        return (bool) $result;
    }

    /**
     * Deletes an order
     * @param integer $order_id
     * @return boolean
     */
    public function delete($order_id)
    {
        $this->hook->fire('order.delete.before', $order_id);

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

        $this->hook->fire('order.delete.after', $order_id, $deleted);
        return (bool) $deleted;
    }

    /**
     * Mark the order ID is viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function setViewed(array $order)
    {
        $user_id = $this->user->getSession('user_id');

        if ($this->isViewed($order, $user_id)) {
            return true; // Record already exists
        }

        $lifespan = $this->config->get('history_lifespan', 2628000);

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
        $sql = 'SELECT history_id FROM history WHERE id_key=? AND id_value=? AND user_id=?';
        $conditions = array('order_id', $order['order_id'], $user_id);

        return (bool) $this->db->fetchColumn($sql, $conditions);
    }

    /**
     * Whether the order has been already viewed by the user
     * @param array $order
     * @return boolean
     */
    public function isNew(array $order)
    {
        $lifespan = $this->config->get('history_lifespan', 2628000);

        if (empty($order['viewed'])) {
            return !((GC_TIME - $order['created']) > $lifespan);
        }

        return ((GC_TIME - $order['viewed']) > $lifespan);
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
        $this->hook->fire('order.submit.before', $data, $cart, $options);

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('An error occurred')
        );

        if (empty($data)) {
            return $result;
        }

        $this->prepareComponents($data, $cart);
        $order_id = $this->add($data);

        if (empty($order_id)) {
            return $result;
        }

        $order = $this->get($order_id);

        if (empty($data['order_id'])) {
            $this->setPriceRule($order);
            $this->updateCart($order, $cart);
        } else {
            $this->cloneCart($order, $cart);
        }

        $result = array(
            'order' => $order,
            'severity' => 'success',
            'redirect' => "admin/sale/order/$order_id",
            'message' => $this->language->text('Order has been created')
        );

        if (empty($options['admin'])) {
            $this->setNotificationCreated($order);
            $result['message'] = '';
            $result['redirect'] = "checkout/complete/$order_id";
        }
        $this->hook->fire('order.submit.after', $order, $result, $cart, $options);
        return $result;
    }

    /**
     * 
     * @param array $order
     * @param array $cart
     */
    protected function cloneCart(array $order, array $cart)
    {
        foreach ($cart['items'] as $item) {
            unset($item['cart_id']);
            $item['user_id'] = $order['user_id'];
            $item['order_id'] = $order['order_id'];
            $this->cart->add($item);
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
     * Notify when an order has been created
     * @param array $order
     * @return mixed
     */
    public function setNotificationCreated(array $order)
    {
        $this->mail->set('order_created_admin', array($order));

        if (is_numeric($order['user_id']) && !empty($order['user_email'])) {
            return $this->mail->set('order_created_customer', array($order));
        }

        return false;
    }

    /**
     * Notify when an order has been updated
     * @param array $order
     * @return mixed
     */
    public function setNotificationUpdated(array $order)
    {
        if (is_numeric($order['user_id']) && !empty($order['user_email'])) {
            return $this->mail->set('order_updated_customer', array($order));
        }

        return false;
    }

    /**
     * Returns default status ID
     * @return string
     */
    public function getDefaultStatus()
    {
        return $this->config->get('order_status', 'pending');
    }

    /**
     * Returns "canceled" status ID
     * @return string
     */
    public function getStatusCanceled()
    {
        return $this->config->get('order_status_canceled', 'canceled');
    }

    /**
     * Returns "processing" status ID
     * @return string
     */
    public function getStatusProcessing()
    {
        return $this->config->get('order_status_processing', 'processing');
    }

    /**
     * Returns initial order status
     * @return string
     */
    public function getStatusInitial()
    {
        $default = $this->getDefaultStatus();
        return $this->config->get('order_status_initial', $default);
    }

    /**
     * Returns awaiting payment order status
     * @return string
     */
    public function getStatusAwaitingPayment()
    {
        return $this->config->get('order_status_awaiting_payment', 'pending_payment');
    }

    /**
     * Wheter the order is pending, i.e before processing
     * @param array $order
     * @return bool
     */
    public function isPending(array $order)
    {
        return strpos($order['status'], 'pending') === 0;
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
        $default = 'Thank you for your order! Order ID: @num, status: @status';
        $message = $this->config->get('order_complete_message', $default);

        $variables = array(
            '@num' => $order['order_id'],
            '@status' => $this->getStatusName($order['status'])
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
        $default = 'Thank you for your order! Order ID: @num, status: @status';
        $message = $this->config->get('order_complete_message_anonymous', $default);

        $variables = array(
            '@num' => $order['order_id'],
            '@status' => $this->getStatusName($order['status'])
        );

        return $this->language->text($message, $variables);
    }

    /**
     * Adds an order
     * @param array $order
     * @return integer
     */
    public function add(array $order)
    {
        $this->hook->fire('order.add.before', $order);

        if (empty($order)) {
            return 0;
        }

        // In case we're cloning an order
        unset($order['order_id']);

        $order['created'] = GC_TIME;
        $order += array('status' => $this->getDefaultStatus());

        if (empty($order['data']['user'])) {
            $order['data']['user'] = $this->getUserData();
        }

        $order['order_id'] = $this->db->insert('orders', $order);
        $this->hook->fire('order.add.after', $order);

        return $order['order_id'];
    }

    /**
     * Adds an order log record to the database
     * @param array $log
     * @return integer
     */
    public function addLog(array $log)
    {
        $log += array('data' => array(), 'created' => GC_TIME);
        return $this->db->insert('order_log', $log);
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
     * @param array $data
     * @return array
     */
    public function calculate(array &$data)
    {
        static $total = 0;

        $total += $data['cart']['total'];

        $components = array();
        foreach (array('shipping', 'payment') as $module) {

            if (isset($data[$module]) && isset($data[$module . '_methods'][$data[$module]]['price'])) {
                $price = $data[$module . '_methods'][$data[$module]]['price'];
                $components[$module] = array('price' => $price);
                $total += $components[$module]['price'];
            }
        }

        $this->pricerule->calculate($total, $data, $components);

        $result = array(
            'total' => $total,
            'components' => $components,
            'currency' => $data['cart']['currency'],
            // Other modules can use these formatted totals
            'total_decimal' => $this->price->decimal($total, $data['cart']['currency']),
            'total_formatted' => $this->price->format($total, $data['cart']['currency']),
            'total_formatted_number' => $this->price->format($total, $data['cart']['currency'], true, false),
        );

        $this->hook->fire('order.calculate', $result, $data);
        return $result;
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
            'pending_payment' => $this->language->text('Awaiting payment'),
            'canceled' => $this->language->text('Canceled'),
            'delivered' => $this->language->text('Delivered'),
            'completed' => $this->language->text('Completed'),
            'processing' => $this->language->text('Processing'),
            'dispatched' => $this->language->text('Dispatched')
        );

        return $statuses;
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
