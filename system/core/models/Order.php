<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook;
use gplcart\core\models\Mail as MailModel,
    gplcart\core\models\Cart as CartModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\PriceRule as PriceRuleModel;

/**
 * Manages basic behaviors and data related to store orders
 */
class Order
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

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
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $price_rule;

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
     * @param Hook $hook
     * @param Config $config
     * @param UserModel $user
     * @param PriceModel $price
     * @param PriceRuleModel $pricerule
     * @param CartModel $cart
     * @param LanguageModel $language
     * @param MailModel $mail
     */
    public function __construct(Hook $hook, Config $config, UserModel $user, PriceModel $price,
            PriceRuleModel $pricerule, CartModel $cart, LanguageModel $language, MailModel $mail)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();

        $this->mail = $mail;
        $this->user = $user;
        $this->cart = $cart;
        $this->price = $price;
        $this->language = $language;
        $this->price_rule = $pricerule;
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

        $order += array(
            'status' => $this->getDefaultStatus());

        $result = $this->db->insert('orders', $order);
        $this->hook->attach('order.add.after', $order, $result, $this);
        return (int) $result;
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
        $this->setCart($result);
        $this->hook->attach('order.get.after', $order_id, $result, $this);
        return $result;
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
        $conditions2 = array('entity' => 'order', 'entity_id' => $order_id);

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
     * Returns an array of orders or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList($data = array())
    {
        $sql = 'SELECT o.*, u.email AS creator,'
                . 'uc.name AS customer_name, uc.email AS customer_email,'
                . 'CONCAT(uc.name, "", uc.email) AS customer,'
                . 'h.created AS viewed, a.country, a.city_id, a.address_1,'
                . 'a.address_2, a.phone, a.postcode, a.first_name,'
                . 'a.middle_name, a.last_name';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(DISTINCT o.order_id)';
        }

        $sql .= ' FROM orders o'
                . ' LEFT JOIN user u ON(o.creator=u.user_id)'
                . ' LEFT JOIN user uc ON(o.user_id=uc.user_id)'
                . ' LEFT JOIN address a ON(o.shipping_address=a.address_id)'
                . ' LEFT JOIN history h ON(h.user_id=? AND h.entity=? AND h.entity_id=o.order_id)'
                . ' WHERE o.order_id IS NOT NULL';

        $conditions = array($this->user->getId(), 'order');

        if (isset($data['store_id'])) {
            $sql .= ' AND o.store_id = ?';
            $conditions[] = (int) $data['store_id'];
        }

        if (isset($data['total'])) {
            $sql .= ' AND o.total = ?';
            $conditions[] = (int) $data['total'];
        }

        if (isset($data['currency'])) {
            $sql .= ' AND o.currency = ?';
            $conditions[] = $data['currency'];
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND o.user_id = ?';
            $conditions[] = $data['user_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND o.status = ?';
            $conditions[] = $data['status'];
        }

        if (isset($data['creator'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$data['creator']}%";
        }

        if (isset($data['shipping_prefix'])) {
            $sql .= ' AND o.shipping LIKE ?';
            $conditions[] = "{$data['shipping_prefix']}%";
        }

        if (isset($data['customer'])) {
            $sql .= ' AND (uc.email LIKE ? OR uc.name LIKE ?)';
            $conditions[] = "%{$data['customer']}%";
            $conditions[] = "%{$data['customer']}%";
        }

        if (isset($data['tracking_number'])) {
            $sql .= ' AND o.tracking_number LIKE ?';
            $conditions[] = "%{$data['tracking_number']}%";
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
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $options = array('unserialize' => 'data', 'index' => 'order_id');
        $orders = $this->db->fetchAll($sql, $conditions, $options);

        $this->hook->attach('order.list', $orders, $this);
        return $orders;
    }

    /**
     * Returns an array of order statuses
     * @return array
     */
    public function getStatuses()
    {
        $statuses = &gplcart_static('order.statuses');

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
     * Sets cart items to the order
     * @param array $order
     */
    protected function setCart(array &$order)
    {
        if (isset($order['order_id'])) {
            $order['cart'] = $this->cart->getList(array('order_id' => $order['order_id']));
        }
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
     * Returns a component type name
     * @param string $name
     * @return string
     */
    public function getComponentType($name)
    {
        $types = $this->getComponentTypes();
        return empty($types[$name]) ? '' : $types[$name];
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

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('An error occurred')
        );

        $this->prepareComponents($data);

        $data['order_id'] = $this->add($data);

        if (empty($data['order_id'])) {
            return $result;
        }

        $order = $this->get($data['order_id']);
        $this->setBundledProducts($order, $data);

        $result = array(
            'order' => $order,
            'severity' => 'success',
            'redirect' => "admin/sale/order/{$data['order_id']}",
            'message' => $this->language->text('Order has been created')
        );

        if (empty($options['admin'])) {

            $this->setPriceRules($order);
            $this->updateCart($order, $data['cart']);
            $this->setNotificationCreatedByCustomer($order);

            $result['message'] = '';
            $result['redirect'] = "checkout/complete/{$data['order_id']}";
        } else {
            $this->cloneCart($order, $data['cart']);
        }

        $this->hook->attach('order.submit.after', $data, $options, $result, $this);
        return (array) $result;
    }

    /**
     * Adds bundled products
     * @param array $order
     * @param array $data
     */
    protected function setBundledProducts(array $order, array $data)
    {
        $update = false;
        foreach ($data['cart']['items'] as $item) {

            if (empty($item['product']['bundled_products'])) {
                continue;
            }

            foreach ($item['product']['bundled_products'] as $product) {

                $cart = array(
                    'sku' => $product['sku'],
                    'user_id' => $data['user_id'],
                    'quantity' => $item['quantity'],
                    'store_id' => $data['store_id'],
                    'order_id' => $order['order_id'],
                    'product_id' => $product['product_id'],
                );

                $update = true;
                $order['data']['components']['cart']['items'][$product['sku']]['price'] = 0;
                $this->cart->add($cart);
            }
        }

        if ($update) {
            $this->update($order['order_id'], array('data' => $order['data']));
        }
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
                'order_id' => $order['order_id'],
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
                continue;
            }

            $rule = $this->price_rule->get($component_id);
            if (!empty($rule['code'])) {
                $this->price_rule->setUsed($rule['price_rule_id']);
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
        $default = /* @text */'Thank you for your order! Order ID: @num, status: @status';
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
        $default = /* @text */'Thank you for your order! Order ID: @num, status: @status';
        $message = $this->config->get('order_complete_message_anonymous', $default);

        $variables = array(
            '@num' => $order['order_id'],
            '@status' => $this->getStatusName($order['status'])
        );

        return $this->language->text($message, $variables);
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

        $components = array();
        $total = $data['cart']['total'];

        $this->calculateComponents($total, $data, $components);
        $this->calculatePriceRules($total, $data, $components);

        $result = array(
            'total' => $total,
            'components' => $components,
            'currency' => $data['cart']['currency'],
            'total_decimal' => $this->price->decimal($total, $data['cart']['currency']),
            'total_formatted' => $this->price->format($total, $data['cart']['currency']),
            'total_formatted_number' => $this->price->format($total, $data['cart']['currency'], true, false),
        );

        $this->hook->attach('order.calculate.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Calculate order components (e.g shipping) using predefined values which can be provided by modules
     * @param int $total
     * @param array $data
     * @param array $components
     */
    protected function calculateComponents(&$total, array &$data, array &$components)
    {
        foreach (array('shipping', 'payment') as $type) {
            if (isset($data['order'][$type]) && isset($data[$type . '_methods'][$data['order'][$type]]['price'])) {
                $price = $data[$type . '_methods'][$data['order'][$type]]['price'];
                $components[$type] = array('price' => $price);
                $total += $components[$type]['price'];
            }
        }
    }

    /**
     * Calculate order price rules
     * @param int $total
     * @param array $data
     * @param array $components
     */
    protected function calculatePriceRules(&$total, array &$data, array &$components)
    {
        $options = array(
            'status' => 1,
            'store_id' => $data['order']['store_id']
        );

        $code = null;
        if (isset($data['order']['data']['pricerule_code'])) {
            $code = $data['order']['data']['pricerule_code'];
        }

        foreach ($this->price_rule->getTriggered($data, $options) as $price_rule) {

            if ($price_rule['code'] !== '' && !isset($code)) {
                $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => 0);
                continue;
            }

            if (isset($code) && !$this->priceRuleCodeMatches($price_rule['price_rule_id'], $code)) {
                $components[$price_rule['price_rule_id']] = array('rule' => $price_rule, 'price' => 0);
                continue;
            }

            $this->price_rule->calculate($total, $data, $components, $price_rule);
        }
    }

    /**
     * Whether a given code matches the price rule
     * @param integer $price_rule_id
     * @param string $code
     * @return boolean
     */
    public function priceRuleCodeMatches($price_rule_id, $code)
    {
        return $this->price_rule->codeMatches($price_rule_id, $code);
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

}
