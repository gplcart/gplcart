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
use gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to collections
 */
class Collection
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
     * Returns an array of collections or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, COALESCE(NULLIF(ct.title, ""), c.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(c.collection_id)';
        }

        $sql .= ' FROM collection c'
                . ' LEFT JOIN collection_translation ct ON(ct.collection_id = c.collection_id AND ct.language=?)'
                . ' WHERE c.collection_id IS NOT NULL';

        $language = $this->translation->getLangcode();
        $conditions = array($language);

        if (isset($data['title'])) {
            $sql .= ' AND (c.title LIKE ? OR (ct.title LIKE ? AND ct.language=?))';
            $conditions[] = "%{$data['title']}%";
            $conditions[] = "%{$data['title']}%";
            $conditions[] = $language;
        }

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND c.store_id = ?';
            $conditions[] = (int) $data['store_id'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND c.type = ?';
            $conditions[] = $data['type'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status', 'type', 'store_id', 'collection_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))
        ) {
            $sql .= " ORDER BY c.{$data['sort']} {$data['order']}";
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'collection_id'));
        $this->hook->attach('collection.list', $list, $this);
        return $list;
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
     * Loads a collection from the database
     * @param integer $collection_id
     * @param null|string $language
     * @return array
     */
    public function get($collection_id, $language = null)
    {
        $result = null;
        $this->hook->attach('collection.get.before', $collection_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->db->fetch('SELECT * FROM collection WHERE collection_id=?', array($collection_id));
        $this->attachTranslations($result, $this->translation_entity, 'collection', $language);

        $this->hook->attach('collection.get.after', $collection_id, $language, $result, $this);
        return $result;
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

        $conditions = array('collection_id' => $collection_id);
        $result = $this->db->delete('collection', $conditions);

        if (!empty($result)) {
            $this->db->delete('collection_translation', $conditions);
        }

        $this->hook->attach('collection.delete.after', $collection_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a collection can be deleted
     * @param integer $collection_id
     * @return boolean
     */
    public function canDelete($collection_id)
    {
        $sql = 'SELECT collection_item_id'
                . ' FROM collection_item'
                . ' WHERE collection_id=?';

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

        unset($data['type']); // Cannot change item type!

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
