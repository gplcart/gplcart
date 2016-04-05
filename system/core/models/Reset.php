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
use core\Config;

/**
 * Manages basic behaviors and data related to resetting the system
 */
class Reset
{

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = $this->config->db();
    }

    /**
     * Resets the database
     */
    public function reset()
    {
        ini_set('max_execution_time', 0);

        $untruncated = array('user', 'country', 'settings', 'store');
        $undeletable_settings = array('user_superadmin', 'country', 'store');

        $tables = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach (array_diff($tables, $untruncated) as $table) {
            $this->db->query("TRUNCATE TABLE $table");
        }

        $superadmin = (int) $this->config->get('user_superadmin', 1);
        $country = $this->db->quote($this->config->get('country', ''));
        $store = (int) $this->config->get('store', 1);
        $settings = implode(',', array_map(array($this->db, 'quote'), $undeletable_settings));

        $this->db->query("DELETE FROM user WHERE user_id <> $superadmin");
        $this->db->query("DELETE FROM country WHERE code <> $country");
        $this->db->query("DELETE FROM store WHERE store_id <> $store");
        $this->db->query("DELETE FROM settings WHERE id NOT IN($settings)");

        $superadmin++;
        $store++;

        $this->db->query("ALTER TABLE user AUTO_INCREMENT = $superadmin");
        $this->db->query("ALTER TABLE store AUTO_INCREMENT = $store");
    }
}
