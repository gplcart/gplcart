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
     * Returns an array of database table names keyed by entity name
     * @return array
     */
    public function getTables()
    {
        return array(
            'category' => 'category_translation',
            'category_group' => 'category_group_translation',
            'page' => 'page_translation',
            'product' => 'product_translation',
            'field' => 'field_translation',
            'field_value' => 'field_value_translation',
            'collection' => 'collection_translation',
            'file' => 'file_translation',
        );
    }

    /**
     * Returns a database table name for the entity
     * @param string $entity
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getTable($entity)
    {
        $tables = $this->getTables();

        if (empty($tables[$entity])) {
            throw new \InvalidArgumentException("No translation table exists for entity '$entity'");
        }

        return $tables[$entity];
    }

    /**
     * Returns an array of translations
     * @param string $entity
     * @param int $entity_id
     * @param string|null $langcode
     * @return array
     */
    public function getList($entity, $entity_id, $langcode = null)
    {
        $table = $this->getTable($entity);

        $sql = "SELECT * FROM $table WHERE {$entity}_id = ?";

        $conditions = array($entity_id);

        if (isset($langcode)) {
            $sql .= ' AND language = ?';
            $conditions[] = $langcode;
        }

        $result = $this->db->fetchAll($sql, $conditions);
        $this->hook->attach('translation.entity.list', $entity, $entity_id, $langcode, $result, $this);
        return (array) $result;
    }

    /**
     * Adds a translation for the entity
     * @param string $entity
     * @param array $data
     * @return int
     */
    public function add($entity, array $data)
    {
        $result = null;
        $this->hook->attach('translation.entity.add.before', $entity, $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert($this->getTable($entity), $data);
        $this->hook->attach('translation.entity.add.after', $entity, $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes translation(s)
     * @param string $entity
     * @param int $entity_id
     * @param null|string $language
     * @return bool
     */
    public function delete($entity, $entity_id, $language = null)
    {
        $result = null;
        $this->hook->attach('translation.entity.delete.before', $entity, $entity_id, $language, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array("{$entity}_id" => $entity_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        $result = (bool) $this->db->delete($this->getTable($entity), $conditions);
        $this->hook->attach('translation.entity.delete.after', $entity, $entity_id, $language, $result, $this);
        return (bool) $result;
    }

}
