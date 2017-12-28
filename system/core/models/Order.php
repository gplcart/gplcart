<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;
use gplcart\core\models\Cart as CartModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\PriceRule as PriceRuleModel,
    gplcart\core\models\Translation as TranslationModel;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

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
     * @param TranslationModel $translation
     */
    public function __construct(Hook $hook, Config $config, UserModel $user, PriceModel $price,
            PriceRuleModel $pricerule, CartModel $cart, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();

        $this->user = $user;
        $this->cart = $cart;
        $this->price = $price;
        $this->price_rule = $pricerule;
        $this->translation = $translation;
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

        $conditions = array(
            'limit' => array(0, 1),
            'order_id' => $order_id
        );

        $list = (array) $this->getList($conditions);
        $result = empty($list) ? array() : reset($list);

        $this->setCart($result);
        $this->hook->attach('order.get.after', $order_id, $result, $this);
        return $result;
    }

    /**
     * Returns an array of orders or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('current_user' => $this->user->getId());

        $result = null;
        $this->hook->attach('order.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT o.*, u.email AS creator_email, u.name AS creator_name,'
                . 'uc.name AS customer_name, uc.email AS customer_email,'
                . 'CONCAT(uc.name, "", uc.email) AS customer,'
                . 'h.created AS viewed, a.country, a.city_id, a.address_1,'
                . 'a.address_2, a.phone, a.postcode, a.first_name,'
                . 'a.middle_name, a.last_name';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(DISTINCT o.order_id)';
        }

        $sql .= ' FROM orders o'
                . ' LEFT JOIN user u ON(o.creator=u.user_id)'
                . ' LEFT JOIN user uc ON(o.user_id=uc.user_id)'
                . ' LEFT JOIN address a ON(o.shipping_address=a.address_id)'
                . ' LEFT JOIN history h ON(h.user_id=? AND h.entity=? AND h.entity_id=o.order_id)';

        $conditions = array($options['current_user'], 'order');

        if (isset($options['order_id'])) {
            $sql .= ' WHERE o.order_id = ?';
            $conditions[] = (int) $options['order_id'];
        } else {
            $sql .= ' WHERE o.order_id IS NOT NULL';
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND o.store_id = ?';
            $conditions[] = (int) $options['store_id'];
        }

        if (isset($options['total'])) {
            $sql .= ' AND o.total = ?';
            $conditions[] = (int) $options['total'];
        }

        if (isset($options['currency'])) {
            $sql .= ' AND o.currency = ?';
            $conditions[] = $options['currency'];
        }

        if (isset($options['user_id'])) {
            $sql .= ' AND o.user_id = ?';
            $conditions[] = $options['user_id'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND o.status = ?';
            $conditions[] = $options['status'];
        }

        if (isset($options['creator'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$options['creator']}%";
        }

        if (isset($options['shipping_prefix'])) {
            $sql .= ' AND o.shipping LIKE ?';
            $conditions[] = "{$options['shipping_prefix']}%";
        }

        if (isset($options['customer'])) {
            $sql .= ' AND (uc.email LIKE ? OR uc.name LIKE ?)';
            $conditions[] = "%{$options['customer']}%";
            $conditions[] = "%{$options['customer']}%";
        }

        if (isset($options['tracking_number'])) {
            $sql .= ' AND o.tracking_number LIKE ?';
            $conditions[] = "%{$options['tracking_number']}%";
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

        if (isset($options['sort']) && isset($allowed_sort[$options['sort']])//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= " ORDER BY o.modified DESC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('unserialize' => 'data', 'index' => 'order_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('order.list.after', $options, $result, $this);
        return $result;
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

        unset($order['order_id']); // In case we're cloning the order
        $order['created'] = $order['modified'] = GC_TIME;
        $order += array('status' => $this->getDefaultStatus());

        $result = $this->db->insert('orders', $order);
        $this->hook->attach('order.add.after', $order, $result, $this);
        return (int) $result;
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

        $result = (bool) $this->db->delete('orders', array('order_id' => $order_id));

        if ($result) {
            $this->deleteLinked($order_id);
        }

        $this->hook->attach('order.delete.after', $order_id, $result, $this);
        return (bool) $result;
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
     * Returns an array of order component types
     * @return array
     */
    public function getComponentTypes()
    {
        $types = array(
            'cart' => $this->translation->text('Cart'),
            'payment' => $this->translation->text('Payment'),
            'shipping' => $this->translation->text('Shipping')
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
     * Returns the checkout complete message
     * @param array $order
     * @return string
     */
    public function getCompleteMessage(array $order)
    {
        $vars = array(
            '@num' => $order['order_id'],
            '@status' => $this->getStatusName($order['status'])
        );

        if (is_numeric($order['user_id'])) {
            $message = $this->translation->text('Thank you for your order! Order ID: @num, status: @status', $vars);
        } else {
            $message = $this->translation->text('Thank you for your order! Order ID: @num, status: @status', $vars);
        }

        $this->hook->attach('order.complete.message', $message, $order, $this);
        return $message;
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
     * Deletes all database records related to the order ID
     * @param int $order_id
     */
    protected function deleteLinked($order_id)
    {
        $this->db->delete('cart', array('order_id' => $order_id));
        $this->db->delete('order_log', array('order_id' => $order_id));
        $this->db->delete('history', array('entity' => 'order', 'entity_id' => $order_id));
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
     * Returns an array of default order statuses
     * @return array
     */
    protected function getDefaultStatuses()
    {
        $statuses = array(
            'pending' => $this->translation->text('Pending'),
            'canceled' => $this->translation->text('Canceled'),
            'delivered' => $this->translation->text('Delivered'),
            'completed' => $this->translation->text('Completed'),
            'processing' => $this->translation->text('Processing'),
            'dispatched' => $this->translation->text('Dispatched'),
            'pending_payment' => $this->translation->text('Awaiting payment')
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
