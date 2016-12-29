<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Cache;
use core\models\Trigger as TriggerModel;
use core\models\Currency as CurrencyModel;
use core\models\Language as LanguageModel;

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
     * Trigger model instance
     * @var \core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Constructor
     * @param CurrencyModel $currency
     * @param TriggerModel $trigger
     * @param LanguageModel $language
     */
    public function __construct(CurrencyModel $currency,
            TriggerModel $trigger, LanguageModel $language)
    {
        parent::__construct();

        $this->trigger = $trigger;
        $this->currency = $currency;
        $this->language = $language;
    }

    /**
     * Returns an array of rules or total number of rules
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $price_rules = &Cache::memory(array('price.rules' => $data));

        if (isset($price_rules)) {
            return $price_rules;
        }

        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(price_rule_id)';
        }

        $sql .= ' FROM price_rule';
        $where = array();

        if (!empty($data['price_rule_id'])) {
            $ids = (array) $data['price_rule_id'];
            $placeholders = rtrim(str_repeat('?, ', count($ids)), ', ');
            $sql .= ' WHERE price_rule_id IN(' . $placeholders . ')';
            $where = array_merge($where, $ids);
        } else {
            $sql .= ' WHERE price_rule_id > 0';
        }

        if (!empty($data['trigger_id'])) {
            $ids = (array) $data['trigger_id'];
            $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
            $sql .= ' AND trigger_id IN(' . $placeholders . ')';
            $where = array_merge($where, $ids);
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

        if (isset($data['currency'])) {
            $sql .= ' AND currency = ?';
            $where[] = $data['currency'];
        }

        $orders = array('asc', 'desc');
        $sorts = array('price_rule_id', 'name', 'code',
            'value', 'value_type', 'weight', 'status', 'currency', 'trigger_id');

        if ((isset($data['sort']) && in_array($data['sort'], $sorts)) && (isset($data['order']) && in_array($data['order'], $orders, true))) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'price_rule_id');
        $price_rules = $this->db->fetchAll($sql, $where, $options);

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

        $sql = 'SELECT * FROM price_rule WHERE price_rule_id=?';
        $price_rule = $this->db->fetch($sql, array($price_rule_id));

        $this->hook->fire('get.price.rule.after', $price_rule);
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

        $data['price_rule_id'] = $this->db->insert('price_rule', $data);

        $this->hook->fire('add.price.rule.after', $data);
        return $data['price_rule_id'];
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

        $conditions = array('price_rule_id' => $price_rule_id);
        $result = $this->db->update('price_rule', $data, $conditions);

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
        $sql = 'UPDATE price_rule SET used=used + 1 WHERE price_rule_id=?';
        return (bool) $this->db->run($sql, array($price_rule_id))->rowCount();
    }

    /**
     * Performs simple rule code validation
     * @param integer $price_rule_id
     * @param string $code
     * @return boolean
     */
    public function codeMatches($price_rule_id, $code)
    {
        $sql = 'SELECT price_rule_id'
                . ' FROM price_rule'
                . ' WHERE code=? AND price_rule_id=? AND status=?';

        $params = array($code, $price_rule_id, 1);
        return (bool) $this->db->fetchColumn($sql, $params);
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

        $conditions = array('price_rule_id' => $price_rule_id);
        $result = $this->db->delete('price_rule', $conditions);

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

        $options = array('store_id' => $data['order']['store_id'], 'status' => 1);
        $rules = $this->getTriggered($options, array('cart' => $cart, 'data' => $data));

        foreach ($rules as $rule) {
            $this->calculateComponent($total, $cart, $data, $components, $rule);
        }
    }

    /**
     * Returns an array of suitable rules for a given context
     * @param array $data
     * @return array
     */
    public function getTriggered(array $options, array $data)
    {
        
        $triggers = $this->trigger->getFired($options, $data);

        if (empty($triggers)) {
            return array();
        }

        $options['trigger_id'] = $triggers;

        $rules = $this->getList($options);

        $coupons = 0;
        $results = array();

        foreach ($rules as $id => $rule) {

            if (!empty($rule['code'])) {
                $coupons++;
            }

            if ($coupons > 1) {
                continue;
            }

            $results[$id] = $rule;
        }

        uasort($results, function ($a, $b) {
            return empty($a['code']) ? -1 : 1; // Coupons always bottom
        });

        return $results;
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

        if ($rule['code'] !== '') {
            if (!isset($data['pricerule_code']) || !$this->codeMatches($rule_id, $data['pricerule_code'])) {
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
