<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Config;
use core\Hook;

/**
 * Manages basic behaviors and data related geo zones
 */
class Zone
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     *
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->hook = $hook;
        $this->db = $this->config->getDb();
    }

    /**
     * Returns a zone
     * @param integer $zone_id
     * @return array
     */
    public function get($zone_id)
    {
        $this->hook->fire('get.zone.before', $zone_id);

        $sth = $this->db->prepare('SELECT * FROM zone WHERE zone_id=:zone_id');
        $sth->execute(array(':zone_id' => $zone_id));

        $zone = $sth->fetch(PDO::FETCH_ASSOC);

        $this->hook->fire('get.zone.after', $zone_id, $zone);
        return $zone;
    }

    /**
     * Adds a zone
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $this->hook->fire('add.zone.before', $data);
        $zone_id = $this->db->insert('zone', $data);
        $this->hook->fire('add.zone.after', $data, $zone_id);
        return $zone_id;
    }

    /**
     * Updates a zone
     * @param integer $zone_id
     * @param array $data
     */
    public function update($zone_id, $data)
    {
        $this->hook->fire('update.zone.before', $zone_id, $data);
        $this->db->update('zone', $data, array('zone_id' => $zone_id));
        $this->hook->fire('update.zone.after', $zone_id, $data);
    }

    /**
     * Deletes a zone
     * @param integer $zone_id
     * @return boolean
     */
    public function delete($zone_id)
    {
        $this->hook->fire('delete.zone.before', $zone_id);
        $this->db->delete('zone', array('zone_id' => $zone_id));
        $this->hook->fire('delete.zone.after', $zone_id);
        return true;
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

        $sth = $this->db->prepare($sql);
        $sth->execute();

        $list = $sth->fetchAll(PDO::FETCH_ASSOC);
        $this->hook->fire('zone.list', $list);
        return $list;
    }
}
