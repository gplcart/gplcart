<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config,
    gplcart\core\Handler;
use gplcart\core\models\Trigger as TriggerModel,
    gplcart\core\models\Currency as CurrencyModel;

/**
 * Manages basic behaviors and data related to price rules
 */
class PriceRule
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
     * @param Hook $hook
     * @param Config $config
     * @param CurrencyModel $currency
     * @param TriggerModel $trigger
     */
    public function __construct(Hook $hook, Config $config, CurrencyModel $currency,
            TriggerModel $trigger)
    {
        $this->hook = $hook;
        $this->trigger = $trigger;
        $this->currency = $currency;
        $this->db = $config->getDb();
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
            return (int) $result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $data['price_rule_id'] = $this->db->insert('price_rule', $data);

        $this->hook->attach('price.rule.add.after', $data, $result, $this);
        return (int) $result;
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
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $result = (bool) $this->db->update('price_rule', $data, array('price_rule_id' => $price_rule_id));

        $this->hook->attach('price.rule.update.after', $price_rule_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of price rules or total number of rules
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $list = &gplcart_static(gplcart_array_hash(array('price.rule.list' => $data)));

        if (isset($list)) {
            return $list;
        }

        $sql = 'SELECT p.*';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.price_rule_id)';
        }

        $sql .= ' FROM price_rule p';

        $conditions = array();

        if (empty($data['price_rule_id'])) {
            $sql .= ' WHERE p.price_rule_id IS NOT NULL';
        } else {
            settype($data['price_rule_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['price_rule_id'])), ',');
            $sql .= " WHERE p.price_rule_id IN($placeholders)";
            $conditions = array_merge($conditions, $data['price_rule_id']);
        }

        if (!empty($data['trigger_id'])) {
            settype($data['trigger_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['trigger_id'])), ',');
            $sql .= " AND p.trigger_id IN($placeholders)";
            $conditions = array_merge($conditions, $data['trigger_id']);
        }

        if (isset($data['name'])) {
            $sql .= ' AND p.name LIKE ?';
            $conditions[] = "%{$data['name']}%";
        }

        if (isset($data['code'])) {
            $sql .= ' AND p.code LIKE ?';
            $conditions[] = "%{$data['code']}%";
        }

        if (isset($data['value'])) {
            $sql .= ' AND p.value = ?';
            $conditions[] = (int) $data['value'];
        }

        if (isset($data['value_type'])) {
            $sql .= ' AND p.value_type = ?';
            $conditions[] = $data['value_type'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND p.status = ?';
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['currency'])) {
            $sql .= ' AND p.currency = ?';
            $conditions[] = $data['currency'];
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
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'price_rule_id'));
        $this->hook->attach('price.rule.list', $data, $list, $this);
        return $list;
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

        return (bool) $this->db->fetchColumn($sql, array($code, $price_rule_id, 1));
    }

    /**
     * Deletes a price rule
     * @param integer $price_rule_id
     * @return boolean
     */
    public function delete($price_rule_id)
    {
        $result = null;
        $this->hook->attach('price.rule.delete.before', $price_rule_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('price_rule', array('price_rule_id' => $price_rule_id));

        $this->hook->attach('price.rule.delete.after', $price_rule_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Calculate a price rule
     * @param int $total
     * @param array $data
     * @param array $components
     * @param array $price_rule
     * @return array
     */
    public function calculate(&$total, $data, &$components, array $price_rule)
    {
        try {
            $callback = Handler::get($this->getTypes(), $price_rule['value_type'], 'calculate');
            call_user_func_array($callback, array(&$total, &$components, $price_rule, $data));
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
        }
    }

    /**
     * Returns an array of price rule types
     * @return array
     */
    public function getTypes()
    {
        $handlers = &gplcart_static('price.rule.types');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = gplcart_config_get(GC_FILE_CONFIG_PRICE_RULE_TYPE);
        $this->hook->attach('price.rule.types', $handlers, $this);
        return $handlers;
    }

    /**
     * Returns an array triggered price rules
     * @param array $data
     * @param array $options
     * @return array
     */
    public function getTriggered(array $data, array $options)
    {
        $options['trigger_id'] = $this->trigger->getTriggered($data, $options);

        if (empty($options['trigger_id'])) {
            return array();
        }

        $coupons = 0;
        $results = array();

        foreach ((array) $this->getList($options) as $id => $rule) {

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
