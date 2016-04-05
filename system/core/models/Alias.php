<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Route;
use core\Config;
use core\classes\Tool;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to URL aliasing
 */
class Alias
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Route class instance
     * @var \core\Route $route
     */
    protected $route;

    /**
     * Config class instance
     * @var core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\class\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param Route $route
     * @param Config $config
     */
    public function __construct(ModelsLanguage $language, Route $route,
                                Config $config)
    {
        $this->language = $language;
        $this->route = $route;
        $this->config = $config;
        $this->db = $this->config->db();
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
        $alias_id = $this->db->insert('alias', array(
            'id_key' => $id_key,
            'id_value' => (int) $id_value,
            'alias' => $alias,
        ));

        return $alias_id;
    }

    /**
     * Returns an alias
     * @param string|integer $id_key
     * @param integer $id_value
     * @return string
     */
    public function get($id_key, $id_value = null)
    {
        if (is_numeric($id_key)) {
            $sth = $this->db->prepare('SELECT * FROM alias WHERE alias_id=:id');
            $sth->execute(array(':id' => (int) $id_key));
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }

        $sth = $this->db->prepare('SELECT alias FROM alias WHERE id_key=? AND id_value=?');
        $sth->execute(array($id_key, (int) $id_value));
        return $sth->fetchColumn();
    }

    /**
     * Deletes an alias
     * @param string|integer $id_key
     * @param integer $id_value
     * @return integer
     */
    public function delete($id_key, $id_value = null)
    {
        if (is_numeric($id_key)) {
            return $this->db->delete('alias', array('alias_id' => $id_key));
        }

        return $this->db->delete('alias', array('id_key' => $id_key, 'id_value' => $id_value));
    }

    /**
     * Returns an array of url aliases keyed by id_value
     * @param string $id_key
     * @param array $id_value
     * @return array
     */
    public function getMultiple($id_key, array $id_value)
    {
        $results = $this->getList(array('id_key' => $id_key, 'id_value' => $id_value));

        $aliases = array();
        foreach ($results as $result) {
            $aliases[$result['id_value']] = $result['alias'];
        }

        return $aliases;
    }

    /**
     * Returns an array of aliases
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(alias_id)';
        }

        $sql .= ' FROM alias WHERE alias_id > 0';

        $where = array();

        if (isset($data['id_key'])) {
            $sql .= ' AND id_key = ?';
            $where[] = $data['id_key'];
        }

        if (isset($data['alias'])) {
            $sql .= ' AND alias LIKE ?';
            $where[] = "%{$data['alias']}%";
        }

        if (!empty($data['id_value'])) {
            $placeholders = rtrim(str_repeat('?, ', count((array) $data['id_value'])), ', ');
            $sql .= " AND id_value IN($placeholders)";
            $where = array_merge($where, (array) $data['id_value']);
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            $allowed_sort = array('id_value', 'id_key', 'alias');

            if (in_array($data['sort'], $allowed_sort)) {
                $sql .= " ORDER BY {$data['sort']} {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY alias DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $alias) {
            $list[$alias['alias_id']] = $alias;
        }

        return $list;
    }

    /**
     * Returns an array of id keys (entity types)
     * @return array
     */
    public function getIdKeys()
    {
        $sth = $this->db->prepare('SELECT id_key FROM alias GROUP BY id_key');
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Creates an alias using a data
     * @param string $pattern
     * @param array $placeholders
     * @param array $data
     * @param boolean $translit
     * @param string $language
     * @return string
     */
    public function generate($pattern, array $placeholders = array(),
                             array $data = array(), $translit = true,
                             $language = null)
    {
        $alias = $pattern;

        if ($placeholders) {
            $alias = Tool::replacePlaceholders($pattern, $placeholders, $data);
        }

        if ($translit) {
            $alias = $this->language->translit($alias, $language);
            $alias = preg_replace('/[^a-z0-9.\-_ ]/', '', strtolower($alias));
        }

        $alias = mb_strimwidth(str_replace(' ', '-', trim($alias)), 0, 100, '', 'UTF-8');

        if ($this->exists($alias)) {
            $info = pathinfo($alias);
            $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

            $counter = 0;
            do {
                $alias = $info['filename'] . '-' . $counter++ . $ext;
            } while ($this->exists($alias));
        }

        return $alias;
    }

    /**
     * Whether the alias path exists
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

        return $this->info($path);
    }

    /**
     * Loads an alias
     * @param string $alias
     * @return array
     */
    public function info($alias)
    {
        $sth = $this->db->prepare('SELECT * FROM alias WHERE alias=:alias');
        $sth->execute(array(':alias' => $alias));
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

}
