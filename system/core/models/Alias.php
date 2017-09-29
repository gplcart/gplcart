<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Route,
    gplcart\core\Model;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to URL aliasing
 */
class Alias extends Model
{

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
     * @param LanguageModel $language
     * @param Route $route
     */
    public function __construct(LanguageModel $language, Route $route)
    {
        parent::__construct();

        $this->route = $route;
        $this->language = $language;
    }

    /**
     * Adds an alias
     * @param string $id_key
     * @param integer $id_value
     * @param string $alias
     * @return integer
     */
    public function add($id_key, $id_value, $alias)
    {
        $values = array(
            'alias' => $alias,
            'id_key' => $id_key,
            'id_value' => $id_value
        );

        return $this->db->insert('alias', $values);
    }

    /**
     * Returns an alias
     * @param string $id_key
     * @param null|integer $id_value
     * @return string|array
     */
    public function get($id_key, $id_value = null)
    {
        if (is_numeric($id_key)) {
            $sql = 'SELECT * FROM alias WHERE alias_id=?';
            return $this->db->fetch($sql, array($id_key));
        }

        $sql = 'SELECT alias FROM alias WHERE id_key=? AND id_value=?';
        return $this->db->fetchColumn($sql, array($id_key, $id_value));
    }

    /**
     * Deletes an alias
     * @param string $id_key
     * @param null|integer $id_value
     * @return integer
     */
    public function delete($id_key, $id_value = null)
    {
        if (is_numeric($id_key)) {
            return $this->db->delete('alias', array('alias_id' => $id_key));
        }

        $conditions = array('id_key' => $id_key, 'id_value' => $id_value);
        return $this->db->delete('alias', $conditions);
    }

    /**
     * Returns an array of url aliases keyed by id_value
     * @param string $id_key
     * @param array $id_value
     * @return array
     */
    public function getMultiple($id_key, array $id_value)
    {
        $conditions = array(
            'id_key' => $id_key,
            'id_value' => $id_value
        );

        $results = $this->getList($conditions);

        $aliases = array();
        foreach ((array) $results as $result) {
            $aliases[$result['id_value']] = $result['alias'];
        }

        return $aliases;
    }

    /**
     * Returns an array of aliases or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(alias_id)';
        }

        $sql .= ' FROM alias WHERE alias_id > 0';

        $where = array();

        if (isset($data['alias_id'])) {
            $sql .= ' AND alias_id = ?';
            $where[] = $data['alias_id'];
        }

        if (isset($data['id_key'])) {
            $sql .= ' AND id_key = ?';
            $where[] = $data['id_key'];
        }

        if (isset($data['alias'])) {
            $sql .= ' AND alias LIKE ?';
            $where[] = "%{$data['alias']}%";
        }

        if (!empty($data['id_value'])) {
            settype($data['id_value'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['id_value'])), ',');
            $sql .= " AND id_value IN($placeholders)";
            $where = array_merge($where, $data['id_value']);
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('id_value', 'id_key', 'alias', 'alias_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order'])//
                && in_array($data['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY alias DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'alias_id');
        return $this->db->fetchAll($sql, $where, $options);
    }

    /**
     * Returns a array of id keys (entity types)
     * @return array
     */
    public function getIdKeys()
    {
        return $this->db->fetchColumnAll('SELECT id_key FROM alias GROUP BY id_key');
    }

    /**
     * Creates an alias using an array of data
     * @param string $pattern
     * @param array $placeholders
     * @param array $data
     * @param boolean $translit
     * @param string $language
     * @return string
     */
    public function generate(
    $pattern, array $placeholders = array(), array $data = array(),
            $translit = true, $language = null
    )
    {
        $alias = $pattern;

        if (!empty($placeholders)) {
            $alias = gplcart_string_replace($pattern, $placeholders, $data);
        }

        if ($translit) {
            $transliterated = $this->language->translit($alias, $language);
            $alias = gplcart_string_slug($transliterated);
        }

        $trimmed = mb_strimwidth(str_replace(' ', '-', trim($alias)), 0, 100, '', 'UTF-8');
        return $this->getUnique($trimmed);
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
     * Generates an alias for an entity
     * @param array $data
     * @param string $entity
     * @return string
     */
    public function generateEntity(array $data, $entity)
    {
        $pattern = $this->config->get("{$entity}_alias_pattern", '%t.html');
        $placeholders = $this->config->get("{$entity}_alias_placeholder", array('%t' => 'title'));

        return $this->generate($pattern, $placeholders, $data, true);
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

        return (bool) $this->getByPath($path);
    }

    /**
     * Loads an alias
     * @param string $alias
     * @return array
     */
    public function getByPath($alias)
    {
        return $this->db->fetch('SELECT * FROM alias WHERE alias=?', array($alias));
    }

}
