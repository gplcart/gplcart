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
use OutOfBoundsException;

/**
 * Manages basic behaviors and data related to entity translations
 */
class TranslationEntity
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
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
    }

    /**
     * Returns an array of translations
     * @param array $options
     * @return array
     */
    public function getList(array $options)
    {
        $result = null;
        $this->hook->attach('translation.entity.list.before', $options, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $table = $this->getTable($options['entity']);
        $sql = "SELECT * FROM $table WHERE {$options['entity']}_id = ?";

        $conditions = array($options['entity_id']);

        if (isset($options['language'])) {
            $sql .= ' AND language = ?';
            $conditions[] = $options['language'];
        }

        $result = $this->db->fetchAll($sql, $conditions);
        $this->hook->attach('translation.entity.list.after', $options, $result, $this);
        return (array) $result;
    }

    /**
     * Adds a translation for the entity
     * @param array $data
     * @return int
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('translation.entity.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert($this->getTable($data['entity']), $data);
        $this->hook->attach('translation.entity.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes translation(s)
     * @param array $conditions
     * @return bool
     */
    public function delete(array $conditions)
    {
        $result = null;
        $this->hook->attach('translation.entity.delete.before', $conditions, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete($this->getTable($conditions['entity']), $conditions);
        $this->hook->attach('translation.entity.delete.after', $conditions, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of database table names keyed by entity name
     * @return array
     */
    public function getTables()
    {
        return array(
            'page' => 'page_translation',
            'file' => 'file_translation',
            'field' => 'field_translation',
            'product' => 'product_translation',
            'category' => 'category_translation',
            'collection' => 'collection_translation',
            'field_value' => 'field_value_translation',
            'category_group' => 'category_group_translation'
        );
    }

    /**
     * Returns a database table name for the entity
     * @param string $entity
     * @return string
     * @throws OutOfBoundsException
     */
    public function getTable($entity)
    {
        $tables = $this->getTables();

        if (empty($tables[$entity])) {
            throw new OutOfBoundsException("Entity $entity is not supported for translations");
        }

        return $tables[$entity];
    }

    /**
     * Whether the entity is supported for translations
     * @param string $entity
     * @return bool
     */
    public function isSupportedEntity($entity)
    {
        $tables = $this->getTables();
        return isset($tables[$entity]);
    }

}
