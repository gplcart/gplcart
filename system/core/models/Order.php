<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Mail as MailModel,
    gplcart\core\models\Cart as CartModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\helpers\Convertor as ConvertorHelper;

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
     * Convertor class instance
     * @var \gplcart\core\helpers\Convertor $convertor
     */
    protected $convertor;

    /**
     * @param UserModel $user
     * @param PriceModel $price
     * @param PriceRuleModel $pricerule
     * @param CartModel $cart
     * @param LanguageModel $language
     * @param MailModel $mail
     * @param ConvertorHelper $convertor
     */
    public function __construct(UserModel $user, PriceModel $price,
            PriceRuleModel $pricerule, CartModel $cart, LanguageModel $language,
            MailModel $mail, ConvertorHelper $convertor)
    {
        parent::__construct();

        $this->mail = $mail;
        $this->user = $user;
        $this->cart = $cart;
        $this->price = $price;
        $this->language = $language;
        $this->convertor = $convertor;
        $this->pricerule = $pricerule;
    }

    /**
     * Returns an array of order statuses
     * @return array
     */
    public function getStatuses()
    {
        $statuses = &gplcart_static(__METHOD__);

        if (isset($statuses)) {
            return $statuses;
        }

        $statuses = $this->getDefaultStatuses();
        $this->hook->attach('order.statuses', $statuses, $this);

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
     * Returns an array of orders or counts them
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

        $where = array($this->user->getId(), 'order_id');

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

        if (isset($data['shipping_prefix'])) {
            $sql .= ' AND o.shipping LIKE ?';
            $where[] = "{$data['shipping_prefix']}%";
        }

        if (isset($data['customer'])) {
            $sql .= ' AND (uc.email LIKE ? OR uc.name LIKE ?)';
            $where[] = "%{$data['customer']}%";
            $where[] = "%{$data['customer']}%";
        }

        if (isset($data['tracking_number'])) {
            $sql .= ' AND o.tracking_number LIKE ?';
            $where[] = "%{$data['tracking_number']}%";
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
            'creator' => 'u.email',
            'tracking_number' => 'o.tracking_number'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY o.modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('unserialize' => 'data', 'index' => 'order_id');
        $orders = $this->db->fetchAll($sql, $where, $options);

        $this->hook->attach('order.list', $orders, $this);
        return $orders;
    }

    /**
     * Loads an order from the database
     * @param integer $order_id
     * @return array
     */
    public function get($order_id)
    {
        $result = null;
        $this->hook->attach('order.get.before', $order_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT o.*, u.name AS user_name, u.email AS user_email'
                . ' FROM orders o'
                . ' LEFT JOIN user u ON(o.user_id=u.user_id)'
                . ' WHERE o.order_id=?';

        $result = $this->db->fetch($sql, array($order_id), array('unserialize' => 'data'));

        $this->attachCart($result);

        $this->hook->attach('order.get.after', $order_id, $result, $this);
        return $result;
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
     * Returns an array of order component types
     * @return array
     */
    public function getComponentTypes()
    {
        $types = array(
            'cart' => $this->language->text('Cart'),
            'payment' => $this->language->text('Payment'),
            'shipping' => $this->language->text('Shipping')
        );

        $this->hook->attach('order.component.types', $types);
        return $types;
    }

    /**
     * Updates an order
     * @param integer $order_id
     * @param array $data
     * @return boolean
     */
    public function update($order_id, array $data)
    {
        $result = null;
        $this->hook->attach('order.update.before', $order_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;

        $this->prepareComponents($data);

        $result = (bool) $this->db->update('orders', $data, array('order_id' => $order_id));
        $this->hook->attach('order.update.after', $order_id, $data, $result, $this);

        return (bool) $result;
    }

    /**
     * Deletes an order
     * @param integer $order_id
     * @return boolean
     */
    public function delete($order_id)
    {
        $result = null;
        $this->hook->attach('order.delete.before', $order_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('order_id' => $order_id);
        $conditions2 = array('id_key' => 'order_id', 'id_value' => $order_id);

        $result = (bool) $this->db->delete('orders', $conditions);

        if ($result) {
            $this->db->delete('cart', $conditions);
            $this->db->delete('order_log', $conditions);
            $this->db->delete('history', $conditions2);
        }

        $this->hook->attach('order.delete.after', $order_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Mark the order is viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function setViewed(array $order)
    {
        $user_id = $this->user->getId();

        if ($this->isViewed($order, $user_id)) {
            return true; // Record already exists
        }

        $lifespan = $this->config->get('history_lifespan', 30*24*60*60);

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
     * Whether the order is already viewed by the user
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
     * Whether the order has not been viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function isNew(array $order)
    {
        $lifespan = $this->config->get('history_lifespan', 30*24*60*60);

        if (empty($order['viewed'])) {
            return !((GC_TIME - $order['created']) > $lifespan);
        }

        return (GC_TIME - $order['viewed']) > $lifespan;
    }

    /**
     * Submits an order
     * @param array $data
     * @param array $options
     * @return array
     */
    public function submit(array $data, array $options = array())
    {
        $result = array();
        $this->hook->attach('order.submit.before', $data, $options, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $this->prepareComponents($data);

        $order_id = $this->add($data);

        if (empty($order_id)) {
            return array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->language->text('An error occurred')
            );
        }

        $order = $this->get($order_id);

        $result = array(
            'order' => $order,
            'severity' => 'success',
            'redirect' => "admin/sale/order/$order_id",
            'message' => $this->language->text('Order has been created')
        );

        if (empty($options['admin'])) {

            $this->setPriceRules($order);
            $this->updateCart($order, $data['cart']);
            $this->setNotificationCreatedByCustomer($order);

            $result['message'] = '';
            $result['redirect'] = "checkout/complete/$order_id";
        } else {
            $this->cloneCart($order, $data['cart']);
        }

        $this->hook->attach('order.submit.after', $data, $options, $result, $this);
        return (array) $result;
    }

    /**
     * Clone an order
     * @param array $order
     * @param array $cart
     */
    protected function cloneCart(array $order, array $cart)
    {
        foreach ($cart['items'] as $item) {
            $cart_id = $item['cart_id'];
            unset($item['cart_id']);
            $item['user_id'] = $order['user_id'];
            $item['order_id'] = $order['order_id'];
            $this->cart->add($item);
            $this->cart->delete($cart_id);
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
    protected function setPriceRules(array $order)
    {
        foreach (array_keys($order['data']['components']) as $component_id) {

            if (!is_numeric($component_id)) {
                continue; // We need only rules
            }

            $rule = $this->pricerule->get($component_id);

            if ($rule['code'] !== '') {
                $this->pricerule->setUsed($rule['price_rule_id']);
            }
        }
    }

    /**
     * Notify when an order has been created
     * @param array $order
     * @return boolean
     */
    public function setNotificationCreatedByCustomer(array $order)
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
     * @return boolean
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
     * Whether the order is pending, i.e before processing
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

        $this->hook->attach('order.complete.message', $message, $order, $this);
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
        $result = null;
        $this->hook->attach('order.add.before', $order, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        // In case we're cloning the order
        unset($order['order_id']);

        $order['created'] = $order['modified'] = GC_TIME;
        $order += array('status' => $this->getDefaultStatus());

        $result = $this->db->insert('orders', $order);

        $this->hook->attach('order.add.after', $order, $result, $this);
        return (int) $result;
    }

    /**
     * Adds an order log record to the database
     * @param array $log
     * @return integer
     */
    public function addLog(array $log)
    {
        $log += array(
            'data' => array(),
            'created' => GC_TIME
        );

        return $this->db->insert('order_log', $log);
    }

    /**
     * Returns an array of log records
     * @param array $data
     * @return array|int
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
     * Returns an order log record
     * @param integer $order_log_id
     * @return array
     */
    public function getLog($order_log_id)
    {
        $sql = 'SELECT ol.*, u.name AS user_name, u.email AS user_email, u.status AS user_status'
                . ' FROM order_log ol'
                . ' LEFT JOIN user u ON(ol.user_id=u.user_id)'
                . ' WHERE ol.order_log_id=?';

        return $this->db->fetch($sql, array($order_log_id), array('unserialize' => 'data'));
    }

    /**
     * Calculates order totals
     * @param array $data
     * @return array
     */
    public function calculate(array &$data)
    {
        $result = array();
        $this->hook->attach('order.calculate.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $total = $data['cart']['total'];

        $components = array();
        foreach (array('shipping', 'payment') as $type) {

            if (isset($data['order'][$type]) && isset($data[$type . '_methods'][$data['order'][$type]]['price'])) {
                $price = $data[$type . '_methods'][$data['order'][$type]]['price'];
                $components[$type] = array('price' => $price);
                $total += $components[$type]['price'];
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

        $this->hook->attach('order.calculate.after', $data, $result, $this);
        return (array) $result;
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
     * Returns an array of default order statuses
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
            'dispatched' => $this->language->text('Dispatched'),
            'pending_payment' => $this->language->text('Awaiting payment')
        );

        return $statuses;
    }

    /**
     * Prepares order components
     * @param array $order
     */
    protected function prepareComponents(array &$order)
    {
        if (!empty($order['cart']['items'])) {
            foreach ($order['cart']['items'] as $sku => $item) {
                $order['data']['components']['cart']['items'][$sku]['price'] = $item['total'];
            }
        }
    }

    /**
     * Returns a total volume of all products in the order
     * @param array $order
     * @param array $cart
     * @param integer $decimals
     * @return float
     */
    public function getVolume(array $order, array $cart, $decimals = 2)
    {
        $total = 0;
        foreach ($cart['items'] as $item) {

            $product = $item['product'];
            if (empty($product['width']) || empty($product['height']) || empty($product['length'])) {
                return (float) 0;
            }

            $volume = $product['width'] * $product['height'] * $product['length'];
            if (empty($product['size_unit']) || $product['size_unit'] == $order['size_unit']) {
                $total += (float) ($volume * $item['quantity']);
                continue;
            }

            $order_cubic = $order['size_unit'] . '2';
            $product_cubic = $product['size_unit'] . '2';
            $converted = $this->convertor->convert($volume, $product_cubic, $order_cubic, $decimals);

            if (empty($converted)) {
                return (float) 0;
            }

            $total += (float) ($converted * $item['quantity']);
        }

        return round($total, $decimals);
    }

    /**
     * Returns a total weight of all products in the order
     * @param array $order
     * @param array $cart
     * @param integer $decimals
     * @return float
     */
    public function getWeight(array $order, array $cart, $decimals = 2)
    {
        $total = 0;
        foreach ($cart['items'] as $item) {

            if (empty($item['product']['weight'])) {
                return (float) 0;
            }

            $product = $item['product'];
            if (empty($product['weight_unit']) || $product['weight_unit'] == $order['weight_unit']) {
                $total += (float) ($product['weight'] * $item['quantity']);
                continue;
            }

            $converted = $this->convertor->convert($product['weight'], $product['weight_unit'], $order['weight_unit'], $decimals);
            if (empty($converted)) {
                return (float) 0;
            }

            $total += (float) ($converted * $item['quantity']);
        }

        return round($total, $decimals);
    }

}
