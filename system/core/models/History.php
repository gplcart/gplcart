<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Database;
use gplcart\core\models\User as UserModel;

/**
 * Manages basic behaviors and data related to entity history
 * Basically it's used to determine if an entity (e.g order) has been viewed by a user
 */
class History
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * User model class instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * @param Database $db
     * @param Config $config
     * @param UserModel $user
     */
    public function __construct(Database $db, Config $config, UserModel $user)
    {
        $this->db = $db;
        $this->user = $user;
        $this->config = $config;
    }

    /**
     * Add a history record
     * @param array $data
     * @return bool
     */
    public function add(array $data)
    {
        $data['created'] = GC_TIME;
        return (bool) $this->db->insert('history', $data);
    }

    /**
     * Set a history record
     * @param string $id_key
     * @param int $id_value
     * @param int $created
     * @param null|int $user_id
     * @return boolean
     */
    public function set($id_key, $id_value, $created, $user_id = null)
    {
        if (!isset($user_id)) {
            $user_id = $this->user->getId(); // Current user
        }

        if ($this->exists($id_key, $id_value, $user_id)) {
            return true;
        }

        if ((GC_TIME - $created) >= $this->getLifespan()) {
            return true; // Expired and was removed
        }

        $data = array(
            'id_key' => $id_key,
            'user_id' => $user_id,
            'id_value' => $id_value
        );

        return $this->add($data);
    }

    /**
     * Whether a record exists in the history table
     * @param string $id_key
     * @param int $id_value
     * @param int $user_id
     * @return bool
     */
    public function exists($id_key, $id_value, $user_id)
    {
        $sql = 'SELECT history_id FROM history WHERE id_key=? AND id_value=? AND user_id=?';
        return (bool) $this->db->fetchColumn($sql, array($id_key, $id_value, $user_id));
    }

    /**
     * Whether an entity is new
     * @param int $entity_creation_time
     * @param int|null $history_creation_time
     * @return bool
     */
    public function isNew($entity_creation_time, $history_creation_time)
    {
        $lifespan = $this->getLifespan();

        if (empty($history_creation_time)) {
            return (GC_TIME - $entity_creation_time) <= $lifespan;
        }

        return (GC_TIME - $history_creation_time) > $lifespan;
    }

    /**
     * Delete all expired records from the history table
     */
    public function deleteExpired()
    {
        $this->db->run('DELETE FROM history WHERE created < ?', array(GC_TIME - $this->getLifespan()));
    }

    /**
     * History record lifespan
     * @return integer
     */
    public function getLifespan()
    {
        return (int) $this->config->get('history_lifespan', 30 * 24 * 60 * 60);
    }

}
