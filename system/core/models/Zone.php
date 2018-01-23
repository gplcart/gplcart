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

/**
 * Manages basic behaviors and data related geo zones
 */
class Zone implements CrudInterface
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
     * Loads a zone from the database
     * @param int $zone_id
     * @return array
     */
    public function get($zone_id)
    {
        $result = null;
        $this->hook->attach('zone.get.before', $zone_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->db->fetch('SELECT * FROM zone WHERE zone_id=?', array($zone_id));
        $this->hook->attach('zone.get.after', $zone_id, $result, $this);
        return $result;
    }

    /**
     * Returns an array of zones or counts them
     * @param array $options
     * @return array|int
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('zone.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(zone_id)';
        }

        $sql .= ' FROM zone WHERE zone_id IS NOT NULL';

        $conditions = array();

        if (isset($options['status'])) {
            $sql .= ' AND status=?';
            $conditions[] = (int) $options['status'];
        }

        if (isset($options['title'])) {
            $sql .= ' AND title LIKE ?';
            $conditions[] = "%{$options['title']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY title ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'zone_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('zone.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a zone
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('zone.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('zone', $data);
        $this->hook->attach('zone.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a zone
     * @param int $zone_id
     * @param array $data
     * @return boolean
     */
    public function update($zone_id, array $data)
    {
        $result = null;
        $this->hook->attach('zone.update.before', $zone_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('zone', $data, array('zone_id' => $zone_id));
        $this->hook->attach('zone.update.after', $zone_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a zone
     * @param int $zone_id
     * @param bool $check
     * @return boolean
     */
    public function delete($zone_id, $check = true)
    {
        $result = null;
        $this->hook->attach('zone.delete.before', $zone_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($zone_id)) {
            return false;
        }

        $result = (bool) $this->db->delete('zone', array('zone_id' => $zone_id));
        $this->hook->attach('zone.delete.after', $zone_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a zone can be deleted
     * @param int $zone_id
     * @return boolean
     */
    public function canDelete($zone_id)
    {
        $sql = 'SELECT NOT EXISTS (SELECT zone_id FROM country WHERE zone_id=:id)
                AND NOT EXISTS (SELECT zone_id FROM state WHERE zone_id=:id)
                AND NOT EXISTS (SELECT zone_id FROM city WHERE zone_id=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $zone_id));
    }

}
