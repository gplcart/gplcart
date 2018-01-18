<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Route,
    gplcart\core\Config;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to URL aliases
 */
class Alias
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
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param Route $route
     * @param LanguageModel $language
     */
    public function __construct(Hook $hook, Config $config, Route $route, LanguageModel $language)
    {
        $this->hook = $hook;
        $this->route = $route;
        $this->config = $config;
        $this->language = $language;
        $this->db = $this->config->getDb();
    }

    /**
     * Adds an alias
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('alias.add.before', $data, $result);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('alias', $data);
        $this->hook->attach('alias.add.after', $data, $result);
        return (int) $result;
    }

    /**
     * Returns an entity alias
     * @param array|int $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('alias.get.before', $condition, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        if (!is_array($condition)) {
            $condition = array('alias_id' => $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('alias.get.after', $condition, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an alias for the entity
     * @param string $entity
     * @param int $entity_id
     * @return null|string
     */
    public function getByEntity($entity, $entity_id)
    {
        $conditions = array(
            'entity' => $entity,
            'entity_id' => $entity_id
        );

        $alias = $this->get($conditions);

        return isset($alias['alias']) ? $alias['alias'] : null;
    }

    /**
     * Deletes an alias
     * @param array|int $condition
     * @return bool
     */
    public function delete($condition)
    {
        $result = null;
        $this->hook->attach('alias.delete.before', $condition, $result);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('alias_id' => $condition);
        }

        $result = $this->db->delete('alias', $condition);
        $this->hook->attach('alias.delete.after', $condition, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of aliases or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('alias.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(alias_id)';
        }

        $sql .= ' FROM alias';

        $conditions = array();

        if (isset($options['alias_id'])) {
            $sql .= ' WHERE alias_id = ?';
            $conditions[] = $options['alias_id'];
        } else {
            $sql .= ' WHERE alias_id IS NOT NULL';
        }

        if (isset($options['entity'])) {
            $sql .= ' AND entity = ?';
            $conditions[] = $options['entity'];
        }

        if (!empty($options['entity_id'])) {
            settype($options['entity_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['entity_id'])), ',');
            $sql .= " AND entity_id IN($placeholders)";
            $conditions = array_merge($conditions, $options['entity_id']);
        }

        if (isset($options['alias'])) {
            $sql .= ' AND alias = ?';
            $conditions[] = $options['alias'];
        }

        if (isset($options['alias_like'])) {
            $sql .= ' AND alias LIKE ?';
            $conditions[] = "%{$options['alias_like']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('entity_id', 'entity', 'alias', 'alias_id');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)//
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY alias DESC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'alias_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('alias.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Returns a array of entities
     * @return array
     */
    public function getEntities()
    {
        return $this->db->fetchColumnAll('SELECT entity FROM alias GROUP BY entity');
    }

    /**
     * Creates an alias using an array of data
     * @param string $pattern
     * @param array $options
     * @return string
     */
    public function generate($pattern, array $options = array())
    {
        $options += array(
            'translit' => true,
            'language' => null,
            'placeholders' => array()
        );

        $result = null;
        $this->hook->attach('alias.generate.before', $pattern, $options, $result);

        if (isset($result)) {
            return (string) $result;
        }

        $alias = $pattern;
        if (!empty($options['placeholders'])) {
            $alias = gplcart_string_replace($pattern, $options['placeholders'], $options);
        }

        if (!empty($options['translit'])) {
            $alias = gplcart_string_slug($this->language->translit($alias, $options['language']));
        }

        $trimmed = mb_strimwidth(str_replace(' ', '-', trim($alias)), 0, 100, '');
        $result = $this->getUnique($trimmed);
        $this->hook->attach('alias.generate.after', $pattern, $options, $result);
        return $result;
    }

    /**
     * Generates an alias for an entity
     * @param string $entity_name
     * @param array $data
     * @return string
     */
    public function generateEntity($entity_name, array $data)
    {
        $data += array('placeholders' => $this->getEntityPatternPlaceholders($entity_name));
        return $this->generate($this->getEntityPattern($entity_name), $data);
    }

    /**
     * Returns default entity alias pattern
     * @param string $entity_name
     * @return string
     */
    public function getEntityPattern($entity_name)
    {
        return $this->config->get("{$entity_name}_alias_pattern", '%t.html');
    }

    /**
     * Returns default entity alias placeholders
     * @param string $entity_name
     * @return array
     */
    public function getEntityPatternPlaceholders($entity_name)
    {
        return $this->config->get("{$entity_name}_alias_placeholder", array('%t' => 'title'));
    }

    /**
     * Returns a unique alias using a base string
     * @param string $alias
     * @return string
     */
    public function getUnique($alias)
    {
        if (!$this->exists($alias)) {
            return $alias;
        }

        $info = pathinfo($alias);
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

        $counter = 0;

        do {
            $counter++;
            $modified = $info['filename'] . '-' . $counter . $ext;
        } while ($this->exists($modified));

        return $modified;
    }

    /**
     * Whether the alias path already exists
     * @param string $path
     * @return boolean
     */
    public function exists($path)
    {
        foreach ($this->route->getList() as $route) {
            if (isset($route['pattern']) && $route['pattern'] === $path) {
                return true;
            }
        }

        return (bool) $this->get(array('alias' => $path));
    }

}
