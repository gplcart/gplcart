<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Config,
    gplcart\core\Hook;

/**
 * Manages basic behaviors and data related geo zones
 */
class Zone extends Model
{

    /**
     * @param Config $config
     * @param Hook $hook
     */
    public function __construct(Config $config, Hook $hook)
    {
        parent::__construct($config, $hook);
    }

    /**
     * Loads a zone from the database
     * @param integer $zone_id
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
     * @param integer $zone_id
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
     * @param integer $zone_id
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
     * @param integer $zone_id
     * @return boolean
     */
    public function canDelete($zone_id)
    {
        $sql = 'SELECT NOT EXISTS (SELECT zone_id FROM country WHERE zone_id=:id)'
                . ' AND NOT EXISTS (SELECT zone_id FROM state WHERE zone_id=:id)'
                . ' AND NOT EXISTS (SELECT zone_id FROM city WHERE zone_id=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $zone_id));
    }

    /**
     * Returns an array of zones or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data)
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(zone_id)';
        }

        $sql .= ' FROM zone WHERE zone_id IS NOT NULL';

        $conditions = array();

        if (isset($data['status'])) {
            $sql .= ' AND status=?';
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['title'])) {
            $sql .= ' AND title LIKE ?';
            $conditions[] = "%{$data['title']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'zone_id'));
        $this->hook->attach('zone.list', $list, $this);

        return $list;
    }

}
