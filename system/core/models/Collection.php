<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\Hook;
use gplcart\core\interfaces\Crud as CrudInterface;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to collections
 */
class Collection implements CrudInterface
{

    use TranslationTrait;

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
     * Translation UI model class instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Translation entity model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation,
                                TranslationEntityModel $translation_entity)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
        $this->translation = $translation;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Loads a collection from the database
     * @param array|int|string $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('collection.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!is_array($condition)) {
            $condition = array('collection_id' => $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('collection.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns an array of collections or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('collection.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT c.*, COALESCE(NULLIF(ct.title, ""), c.title) AS title';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(c.collection_id)';
        }

        $sql .= ' FROM collection c
                  LEFT JOIN collection_translation ct ON(ct.collection_id = c.collection_id AND ct.language=?)';

        $conditions = array($options['language']);

        if (isset($options['collection_id'])) {
            $sql .= ' WHERE c.collection_id = ?';
            $conditions[] = $options['collection_id'];
        } else {
            $sql .= ' WHERE c.collection_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (c.title LIKE ? OR (ct.title LIKE ? AND ct.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND c.status = ?';
            $conditions[] = (int) $options['status'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND c.store_id = ?';
            $conditions[] = $options['store_id'];
        }

        if (isset($options['type'])) {
            $sql .= ' AND c.type = ?';
            $conditions[] = $options['type'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status', 'type', 'store_id', 'collection_id');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY c.{$options['sort']} {$options['order']}";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'collection_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('collection.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a collection
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('collection.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $data['collection_id'] = $this->db->insert('collection', $data);
        $this->setTranslations($data, $this->translation_entity, 'collection', false);

        $this->hook->attach('collection.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a collection
     * @param integer $collection_id
     * @param bool $check
     * @return boolean
     */
    public function delete($collection_id, $check = true)
    {
        $result = null;
        $this->hook->attach('collection.delete.before', $collection_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($collection_id)) {
            return false;
        }

        if ($this->db->delete('collection', array('collection_id' => $collection_id))) {
            $this->deleteLinked($collection_id);
        }

        $this->hook->attach('collection.delete.after', $collection_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Delete all database records associated with the collection
     * @param $collection_id
     */
    protected function deleteLinked($collection_id)
    {
        $this->db->delete('collection_translation', array('collection_id' => $collection_id));
    }

    /**
     * Whether a collection can be deleted
     * @param integer $collection_id
     * @return boolean
     */
    public function canDelete($collection_id)
    {
        $sql = 'SELECT collection_item_id FROM collection_item WHERE collection_id=?';
        $result = $this->db->fetchColumn($sql, array($collection_id));
        return empty($result);
    }

    /**
     * Updates a collection
     * @param integer $collection_id
     * @param array $data
     * @return boolean
     */
    public function update($collection_id, array $data)
    {
        $result = null;
        $this->hook->attach('collection.update.before', $collection_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        unset($data['type']); // Cannot change item type

        $updated = $this->db->update('collection', $data, array('collection_id' => $collection_id));
        $data['collection_id'] = $collection_id;
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'collection');

        $result = $updated > 0;
        $this->hook->attach('collection.update.after', $collection_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of collection handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static('collection.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = (array) gplcart_config_get(GC_FILE_CONFIG_COLLECTION);
        $this->hook->attach('collection.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Returns an array of collection type names keyed by a handler ID
     * @return array
     */
    public function getTypes()
    {
        $handlers = $this->getHandlers();

        $types = array();
        foreach ($handlers as $handler_id => $handler) {
            $types[$handler_id] = $handler['title'];
        }

        return $types;
    }

}
