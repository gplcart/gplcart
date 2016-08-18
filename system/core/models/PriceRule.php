<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Model;
use core\Handler;
use core\classes\Tool;
use core\classes\Cache;
use core\models\Currency as ModelsCurrency;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to price rules
 */
class PriceRule extends Model
{

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsCurrency $currency
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsCurrency $currency,
            ModelsLanguage $language)
    {
        parent::__construct();

        $this->currency = $currency;
        $this->language = $language;
    }

    /**
     * Returns an array of condition operators
     * @return array
     */
    public function getConditionOperators()
    {
        return array(">=", "<=", ">", "<", "=", "!=");
    }

    /**
     * Returns array of instance/method of the condition handler
     * @param string $condition_id
     * @param string $method
     * @return mixed
     */
    public function getConditionHandler($condition_id, $method)
    {
        $handlers = $this->getConditionHandlers();
        return Handler::get($handlers, $condition_id, $method);
    }

    /**
     * Returns an array of condition handlers
     * @return array
     */
    public function getConditionHandlers()
    {
        $conditions = &Cache::memory('pricerule.condition.handlers');

        if (isset($conditions)) {
            return $conditions;
        }

        $conditions = array(
            'user_id' => array(
                'description' => $this->language->text('User ID. Parameters: list of numeric IDs, separated by comma'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'userId'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'userId'),
                ),
            ),
            'user_role_id' => array(
                'description' => $this->language->text('User role ID. Parameters: list of numeric IDs, separated by comma'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'userRole'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'userRole'),
                ),
            ),
            'date' => array(
                'description' => $this->language->text('Date. Parameters: One value in time format'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'date'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'date'),
                ),
            ),
            'product_id' => array(
                'description' => $this->language->text('Product ID. Parameters: list of numeric IDs, separated by comma'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'productId'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'productId'),
                ),
            ),
            'product_category_id' => array(
                'description' => $this->language->text('Product catalog category ID. Parameters: list of numeric IDs, separated by comma'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'categoryId'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'categoryId'),
                ),
            ),
            'product_brand_category_id' => array(
                'description' => $this->language->text('Product brand category ID. Parameters: list of numeric IDs, separated by comma'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'brandCategoryId'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'categoryId'),
                ),
            ),
            'used' => array(
                'description' => $this->language->text('Times used. Parameters: One numeric value'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'used'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'used'),
                ),
            ),
            'shipping' => array(
                'description' => $this->language->text('Order shipping method. Parameters: Shipping service ID'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'shipping'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'shipping'),
                ),
            ),
            'payment' => array(
                'description' => $this->language->text('Order payment method. Parameters: Payment service ID'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'payment'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'payment'),
                ),
            ),
            'shipping_address_id' => array(
                'description' => $this->language->text('Order shipping address ID. Parameters: list of numeric IDs, separated by comma'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'shippingAddressId'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'shippingAddressId'),
                ),
            ),
            'country' => array(
                'description' => $this->language->text('Order country code. Parameters: Country code'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'country'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'country'),
                ),
            ),
            'state' => array(
                'description' => $this->language->text('Order state code. Parameters: State code'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'state'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'state'),
                ),
            ),
            'cart_total' => array(
                'description' => $this->language->text('Cart total (order subtotal). Parameters: numeric value'),
                'handlers' => array(
                    'condition' => array('core\\handlers\\pricerule\\Condition', 'cartTotal'),
                    'validate' => array('core\\handlers\\validator\\PriceRule', 'price'),
                ),
            ),
        );

        $this->hook->fire('pricerule.condition.handlers', $conditions);
        return $conditions;
    }

    /**
     * Returns an array of rules or total number of rules
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $price_rules = &Cache::memory('price.rules.' . md5(serialize($data)));

        if (isset($price_rules)) {
            return $price_rules;
        }

        $price_rules = array();

        $sql = 'SELECT * ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(price_rule_id) ';
        }

        $sql .= 'FROM price_rule';
        $where = array();

        if (!empty($data['price_rule_id'])) {
            $ids = (array) $data['price_rule_id'];
            $placeholders = rtrim(str_repeat('?, ', count($ids)), ', ');
            $sql .= ' WHERE price_rule_id IN(' . $placeholders . ')';
            $where = array_merge($where, $ids);
        } else {
            $sql .= ' WHERE price_rule_id IS NOT NULL';
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['code'])) {
            $sql .= ' AND code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        if (isset($data['value'])) {
            $sql .= ' AND value = ?';
            $where[] = (int) $data['value'];
        }

        if (isset($data['value_type'])) {
            $sql .= ' AND value_type = ?';
            $where[] = $data['value_type'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND type = ?';
            $where[] = $data['type'];
        }

        if (isset($data['currency'])) {
            $sql .= ' AND currency = ?';
            $where[] = $data['currency'];
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            $order = $data['order'];

            switch ($data['sort']) {
                case 'price_rule_id':
                    $sql .= " ORDER BY price_rule_id $order";
                    break;
                case 'name':
                    $sql .= " ORDER BY name $order";
                    break;
                case 'code':
                    $sql .= " ORDER BY code $order";
                    break;
                case 'value':
                    $sql .= " ORDER BY value $order";
                    break;
                case 'value_type':
                    $sql .= " ORDER BY value_type $order";
                    break;
                case 'weight':
                    $sql .= " ORDER BY weight $order";
                    break;
                case 'status':
                    $sql .= " ORDER BY status $order";
                    break;
                case 'currency':
                    $sql .= " ORDER BY currency $order";
                    break;
                case 'type':
                    $sql .= " ORDER BY type $order";
                    break;
                case 'store_id':
                    $sql .= " ORDER BY store_id $order";
                    break;
            }
        } else {
            $sql .= ' ORDER BY weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $result) {
            $result['data'] = unserialize($result['data']);

            if (!empty($result['data']['conditions'])) {
                Tool::sortWeight($result['data']['conditions']);
            }

            $price_rules[$result['price_rule_id']] = $result;
        }

        $this->hook->fire('price.rules', $data, $price_rules);
        return $price_rules;
    }

    /**
     * Loads a price rule from the database
     * @param integer $price_rule_id
     * @return array
     */
    public function get($price_rule_id)
    {
        $this->hook->fire('get.price.rule.before', $price_rule_id);

        $sth = $this->db->prepare('SELECT * FROM price_rule WHERE price_rule_id=:price_rule_id');
        $sth->execute(array(':price_rule_id' => (int) $price_rule_id));

        $price_rule = $sth->fetch(PDO::FETCH_ASSOC);

        if (isset($price_rule['data'])) {
            $price_rule['data'] = unserialize($price_rule['data']);
        }

        $this->hook->fire('get.price.rule.after', $price_rule_id, $price_rule);
        return $price_rule;
    }

    /**
     * Adds a price rule
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.price.rule.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'name' => $data['name'],
            'code' => $data['code'],
            'store_id' => !empty($data['store_id']) ? (int) $data['store_id'] : $this->config->get('store', 1),
            'status' => !empty($data['status']),
            'weight' => !empty($data['weight']) ? (int) $data['weight'] : 0,
            'data' => !empty($data['data']) ? serialize($data['data']) : serialize(array()),
            'value' => (int) $data['value'],
            'value_type' => $data['value_type'],
            'type' => $data['type'],
            'currency' => $data['currency']
        );

        $price_rule_id = $this->db->insert('price_rule', $values);
        $this->hook->fire('add.price.rule.after', $data, $price_rule_id);
        return $price_rule_id;
    }

    /**
     * Updates a price rule
     * @param integer $price_rule_id
     * @param array $data
     * @return boolean
     */
    public function update($price_rule_id, array $data)
    {
        $this->hook->fire('update.price.rule.before', $price_rule_id, $data);

        if (empty($price_rule_id)) {
            return false;
        }

        $values = array();

        if (isset($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (isset($data['store_id'])) {
            $values['store_id'] = (int) $data['store_id'];
        }

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (isset($data['code'])) {
            $values['code'] = $data['code'];
        }

        if (isset($data['currency'])) {
            $values['currency'] = $data['currency'];
        }

        if (isset($data['type'])) {
            $values['type'] = $data['type'];
        }

        if (isset($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['value'])) {
            $values['value'] = (int) $data['value'];
        }

        if (isset($data['value_type'])) {
            $values['value_type'] = $data['value_type'];
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('price_rule', $values, array('price_rule_id' => (int) $price_rule_id));
        }

        $this->hook->fire('update.price.rule.after', $price_rule_id, $data, $result);

        return (bool) $result;
    }

    /**
     * Increments a number of usages by 1
     * @param integer $price_rule_id
     * @return boolean
     */
    public function setUsed($price_rule_id)
    {
        $sth = $this->db->prepare('UPDATE price_rule SET used=used + 1 WHERE price_rule_id=:price_rule_id');
        $sth->execute(array(':price_rule_id' => (int) $price_rule_id));
        return (bool) $sth->rowCount();
    }

    /**
     * Performs simple rule code validation
     * @param integer $price_rule_id
     * @param string $code
     * @return boolean
     */
    public function codeMatches($price_rule_id, $code)
    {
        $sql = 'SELECT price_rule_id
               FROM price_rule
               WHERE code=:code AND price_rule_id=:price_rule_id AND status=:status';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':code' => $code, ':price_rule_id' => $price_rule_id, ':status' => 1));
        return (bool) $sth->fetchColumn();
    }

    /**
     * Deletes a price value
     * @param integer $price_rule_id
     * @return boolean
     */
    public function delete($price_rule_id)
    {
        $this->hook->fire('delete.price.rule.before', $price_rule_id);

        if (empty($price_rule_id)) {
            return false;
        }

        $result = $this->db->delete('price_rule', array('price_rule_id' => (int) $price_rule_id));

        $this->hook->fire('delete.price.rule.after', $price_rule_id, $result);
        return (bool) $result;
    }

    /**
     * Applies all suited rules and calculates order totals
     * @param integer $total
     * @param array $cart
     * @param array $data
     * @param array $components
     */
    public function calculate(&$total, array $cart, array $data,
            array &$components)
    {
        $rules = $this->getSuited('order', array('cart' => $cart, 'data' => $data));

        foreach ($rules as $rule) {
            try {
                $this->calculateComponent($total, $cart, $data, $components, $rule);
            } catch (\core\exceptions\UsagePriceRule $exception) {
                throw $exception;
            }
        }
    }

    /**
     * Returns an array of suitable rules for a given context
     * @param string $type
     * @param array $data
     * @return array
     */
    public function getSuited($type, array $data)
    {
        $this->hook->fire('suited.price.rules.before', $type, $data);

        if (isset($data['data']['order']['store_id'])) {
            $store_id = $data['data']['order']['store_id'];
        } else {
            $store_id = $data['store_id'];
        }

        $rules = $this->getList(array(
            'status' => 1,
            'type' => $type,
            'store_id' => $store_id
        ));

        $coupons = 0;
        $results = array();

        foreach ($rules as $id => $rule) {
            if (!$this->conditionsMet($rule, $data)) {
                continue;
            }

            if ($type === 'order' && !empty($rule['code'])) {
                $coupons++;

                if ($coupons > 1) {
                    continue;
                }
            }

            $results[$id] = $rule;
        }

        uasort($results, function ($a, $b) {
            return empty($a['code']) ? -1 : 1; // Coupons always bottom
        });

        $this->hook->fire('suited.price.rules.after', $results, $data);
        return $results;
    }

    /**
     * Compares numeric values using operators
     * @param integer $value1
     * @param mixed $value2
     * @param string $operator
     * @return boolean
     */
    public function compareNumeric($value1, $value2, $operator)
    {
        if (!is_numeric($value1)) {
            throw new \core\exceptions\UsagePriceRule('Value 1 is not numeric');
        }

        if (!is_numeric($value2) && !is_array($value2)) {
            throw new \core\exceptions\UsagePriceRule('Value 2 neither numeric nor array');
        }

        switch ($operator) {
            case '>=':
                return ($value1 >= $value2);
            case '<=':
                return ($value1 <= $value2);
            case '>':
                return ($value1 > $value2);
            case '<':
                return ($value1 < $value2);
            case '=':

                if (is_array($value2)) {
                    return in_array($value1, $value2);
                }

                return ($value1 == $value2);

            case '!=':
                if (is_array($value2)) {
                    return !in_array($value1, $value2);
                }

                return ($value1 != $value2);
        }

        return false;
    }

    /**
     * Compares string values using operators
     * @param integer $value1
     * @param mixed $value2
     * @param string $operator
     * @return boolean
     */
    public function compareString($value1, $value2, $operator)
    {
        if (!is_string($value1)) {
            throw new \core\exceptions\UsagePriceRule('Value 1 is not string');
        }

        if (!is_string($value2) && !is_array($value2)) {
            throw new \core\exceptions\UsagePriceRule('Value 2 neither string nor array');
        }

        switch ($operator) {
            case '=':

                if (is_array($value2)) {
                    return in_array($value1, $value2, true);
                }

                return (strcmp($value1, $value2) === 0);

            case '!=':
                if (is_array($value2)) {
                    return !in_array($value1, $value2, true);
                }

                return (strcmp($value1, $value2) !== 0);
        }

        return false;
    }

    /**
     * Returns true if conditions are met for a given rule
     * @param array $rule
     * @param array $data
     * @return boolean
     */
    protected function conditionsMet(array $rule, array $data)
    {
        if (empty($rule['data']['conditions'])) {
            return true;
        }

        $handlers = $this->getConditionHandlers();

        Tool::sortWeight($rule['data']['conditions']);

        $results = 0;
        foreach ($rule['data']['conditions'] as $condition) {
            $result = Handler::call($handlers, $condition['id'], 'condition', array($rule, $condition, $data));

            if ($result === true) {
                $results++;
            }
        }

        return (count($rule['data']['conditions']) === $results);
    }

    /**
     * Calculates a price rule component
     * @param integer $amount
     * @param array $cart
     * @param array $data
     * @param array $components
     * @param array $rule
     * @return integer
     */
    protected function calculateComponent(&$amount, array $cart, array $data,
            array &$components, array $rule)
    {
        $rule_id = $rule['price_rule_id'];

        if ($rule['type'] === 'order' && !empty($rule['code'])) {
            if (empty($data['order']['code']) || !$this->codeMatches($rule_id, $data['order']['code'])) {
                $components[$rule_id] = array('rule' => $rule, 'price' => 0);
                return $amount;
            }
        }

        if ($rule['value_type'] === 'percent') {
            $value = $amount * ((float) $rule['value'] / 100);
            $components[$rule_id] = array('rule' => $rule, 'price' => $value);
            $amount += $value;
            return $amount;
        }

        $value = (int) $rule['value'];

        if ($cart['currency'] !== $rule['currency']) {
            $converted = $this->currency->convert(abs($value), $rule['currency'], $cart['currency']);
            $value = ($value < 0) ? -$converted : $converted;
        }

        $components[$rule_id] = array('rule' => $rule, 'price' => $value);
        $amount += $value;
        return $amount;
    }

}
