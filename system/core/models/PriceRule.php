<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Trigger as TriggerModel,
    gplcart\core\models\Currency as CurrencyModel;

/**
 * Manages basic behaviors and data related to price rules
 */
class PriceRule extends Model
{

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * @param CurrencyModel $currency
     * @param TriggerModel $trigger
     */
    public function __construct(CurrencyModel $currency, TriggerModel $trigger)
    {
        parent::__construct();

        $this->trigger = $trigger;
        $this->currency = $currency;
    }

    /**
     * Returns an array of rules or total number of rules
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $list = &gplcart_static(array(__METHOD__ => $data));

        if (isset($list)) {
            return $list;
        }

        $sql = 'SELECT p.*';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.price_rule_id)';
        }

        $sql .= ' FROM price_rule p';

        $where = array();

        if (empty($data['price_rule_id'])) {
            $sql .= ' WHERE p.price_rule_id IS NOT NULL';
        } else {
            settype($data['price_rule_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['price_rule_id'])), ',');
            $sql .= " WHERE p.price_rule_id IN($placeholders)";
            $where = array_merge($where, $data['price_rule_id']);
        }

        if (!empty($data['trigger_id'])) {
            settype($data['trigger_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['trigger_id'])), ',');
            $sql .= " AND p.trigger_id IN($placeholders)";
            $where = array_merge($where, $data['trigger_id']);
        }

        if (isset($data['name'])) {
            $sql .= ' AND p.name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['code'])) {
            $sql .= ' AND p.code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        if (isset($data['value'])) {
            $sql .= ' AND p.value = ?';
            $where[] = (int)$data['value'];
        }

        if (isset($data['value_type'])) {
            $sql .= ' AND p.value_type = ?';
            $where[] = $data['value_type'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND p.status = ?';
            $where[] = (int)$data['status'];
        }

        if (isset($data['currency'])) {
            $sql .= ' AND p.currency = ?';
            $where[] = $data['currency'];
        }

        $orders = array('asc', 'desc');

        $sorts = array(
            'price_rule_id' => 'p.price_rule_id',
            'name' => 'p.name',
            'code' => 'p.code',
            'value' => 'p.value',
            'value_type' => 'p.value_type',
            'weight' => 'p.weight',
            'status' => 'p.status',
            'currency' => 'p.currency',
            'trigger_id' => 'p.trigger_id',
            'created' => 'p.created',
            'modified' => 'p.modified'
        );

        if ((isset($data['sort']) && isset($sorts[$data['sort']]))//
            && (isset($data['order']) && in_array($data['order'], $orders, true))) {
            $sql .= " ORDER BY {$sorts[$data['sort']]} {$data['order']}";
        } else {
            $sql .= ' ORDER BY p.weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int)$this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'price_rule_id'));

        $this->hook->attach('price.rule.list', $data, $list, $this);
        return $list;
    }

    /**
     * Loads a price rule from the database
     * @param integer $price_rule_id
     * @return array
     */
    public function get($price_rule_id)
    {
        $result = null;
        $this->hook->attach('price.rule.get.before', $price_rule_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM price_rule WHERE price_rule_id=?';
        $result = $this->db->fetch($sql, array($price_rule_id));

        $this->hook->attach('price.rule.get.after', $result, $this);
        return $result;
    }

    /**
     * Adds a price rule
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('price.rule.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int)$result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $data['price_rule_id'] = $this->db->insert('price_rule', $data);

        $this->hook->attach('price.rule.add.after', $data, $result, $this);
        return (int)$result;
    }

    /**
     * Updates a price rule
     * @param integer $price_rule_id
     * @param array $data
     * @return boolean
     */
    public function update($price_rule_id, array $data)
    {
        $result = null;
        $this->hook->attach('price.rule.update.before', $price_rule_id, $data, $result, $this);

        if (isset($result)) {
            return (bool)$result;
        }

        $data['modified'] = GC_TIME;
        $result = (bool)$this->db->update('price_rule', $data, array('price_rule_id' => $price_rule_id));

        $this->hook->attach('price.rule.update.after', $price_rule_id, $data, $result, $this);
        return (bool)$result;
    }

    /**
     * Increments a number of usages by 1
     * @param integer $price_rule_id
     * @return boolean
     */
    public function setUsed($price_rule_id)
    {
        $sql = 'UPDATE price_rule SET used=used + 1 WHERE price_rule_id=?';
        return (bool)$this->db->run($sql, array($price_rule_id))->rowCount();
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

        return (bool)$this->db->fetchColumn($sql, array($code, $price_rule_id, 1));
    }

    /**
     * Deletes a price value
     * @param integer $price_rule_id
     * @return boolean
     */
    public function delete($price_rule_id)
    {
        $result = null;
        $this->hook->attach('price.rule.delete.before', $price_rule_id, $result, $this);

        if (isset($result)) {
            return (bool)$result;
        }

        $result = (bool)$this->db->delete('price_rule', array('price_rule_id' => $price_rule_id));

        $this->hook->attach('price.rule.delete.after', $price_rule_id, $result, $this);
        return (bool)$result;
    }

    /**
     * Applies all suited rules and calculates totals
     * @param integer $total
     * @param array $data
     * @param array $components
     * @return array
     */
    public function calculate(&$total, $data, &$components = array())
    {
        $options = array(
            'status' => 1,
            'store_id' => $data['store_id']
        );

        foreach ($this->getTriggered($options, $data) as $rule) {
            $this->calculateComponent($total, $data, $components, $rule);
        }

        return array('total' => $total, 'components' => $components);
    }

    /**
     * Calculates a price rule component
     * @param integer $amount
     * @param array $data
     * @param array $components
     * @param array $rule
     * @return integer
     */
    protected function calculateComponent(&$amount, $data, &$components, $rule)
    {
        $rule_id = $rule['price_rule_id'];

        if ($rule['code'] !== '') {
            if (!isset($data['order']['data']['pricerule_code']) || !$this->codeMatches($rule_id, $data['order']['data']['pricerule_code'])) {
                $components[$rule_id] = array('rule' => $rule, 'price' => 0);
                return $amount;
            }
        }

        if ($rule['value_type'] === 'percent') {
            $value = $amount * ((float)$rule['value'] / 100);
            $components[$rule_id] = array('rule' => $rule, 'price' => $value);
            $amount += $value;
            return $amount;
        }

        if ($data['currency'] != $rule['currency']) {
            $converted = $this->currency->convert(abs($rule['value']), $rule['currency'], $data['currency']);
            $rule['value'] = ($rule['value'] < 0) ? -$converted : $converted;
        }

        $components[$rule_id] = array('rule' => $rule, 'price' => $rule['value']);
        $amount += $rule['value'];

        $this->hook->attach('price.rule.calculate.component', $amount, $data, $components, $rule, $this);
        return $amount;
    }

    /**
     * Returns an array of suitable rules for a given context
     * @param array $options
     * @param array $data
     * @return array
     */
    public function getTriggered(array $options, array $data)
    {
        $options['trigger_id'] = $this->trigger->getFired($options, $data);

        if (empty($options['trigger_id'])) {
            return array();
        }

        $coupons = 0;
        $results = array();

        foreach ((array)$this->getList($options) as $id => $rule) {

            if ($rule['code'] !== '') {
                $coupons++;
            }

            if ($coupons <= 1) {
                $results[$id] = $rule;
            }
        }

        // Coupons always go last
        uasort($results, function ($a) {
            return $a['code'] === '' ? -1 : 1;
        });

        return $results;
    }

}
