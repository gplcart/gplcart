<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;

/**
 * Manages basic behaviors and data related geo zones
 */
class Zone extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a zone
     * @param integer $zone_id
     * @return array
     */
    public function get($zone_id)
    {
        $this->hook->fire('get.zone.before', $zone_id);

        $sql = 'SELECT * FROM zone WHERE zone_id=?';
        $zone = $this->db->fetch($sql, array($zone_id));

        $this->hook->fire('get.zone.after', $zone);

        return $zone;
    }

    /**
     * Adds a zone
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.zone.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['zone_id'] = $this->db->insert('zone', $data);

        $this->hook->fire('add.zone.after', $data);

        return $data['zone_id'];
    }

    /**
     * Updates a zone
     * @param integer $zone_id
     * @param array $data
     * @return boolean
     */
    public function update($zone_id, array $data)
    {
        $this->hook->fire('update.zone.before', $zone_id, $data);

        if (empty($zone_id) || empty($data)) {
            return false;
        }

        $result = (bool) $this->db->update('zone', $data, array('zone_id' => $zone_id));

        $this->hook->fire('update.zone.after', $zone_id, $data, $result);

        return (bool) $result;
    }

    /**
     * Deletes a zone
     * @param integer $zone_id
     * @return boolean
     */
    public function delete($zone_id)
    {
        $this->hook->fire('delete.zone.before', $zone_id);

        $result = (bool) $this->db->delete('zone', array('zone_id' => $zone_id));

        $this->hook->fire('delete.zone.after', $zone_id, $result);

        return (bool) $result;
    }

    /**
     * Returns an array of zones
     * @param boolean $enabled
     * @return array
     */
    public function getList($enabled = false)
    {
        $sql = 'SELECT * FROM zone';

        if ($enabled) {
            $sql .= ' WHERE status > 0';
        }

        $sql .= ' ORDER BY name ASC';

        $list = $this->db->fetchAll($sql, array());

        $this->hook->fire('zone.list', $list);

        return $list;
    }

}
