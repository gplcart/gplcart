<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Config;
use gplcart\core\Handler;
use gplcart\core\Hook;


/**
 * Manages basic behaviors and data related admin dashboard
 */
class Dashboard
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
     * Returns a dashboard record by a user ID
     * @param array $options
     * @return array
     */
    public function getList(array $options)
    {
        $options += array('active' => true);

        $sql = 'SELECT * FROM dashboard';

        $conditions = array();

        if (isset($options['user_id'])) {
            $sql .= ' WHERE user_id=?';
            $conditions[] = $options['user_id'];
        }

        $list = $this->db->fetch($sql, $conditions, array('unserialize' => 'data'));
        $result = $this->prepareList($list, $options);

        $this->hook->attach('dashboard.get.user', $result, $this);
        return $result;
    }

    /**
     * Adds a dashboard record
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('dashboard.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('dashboard', $data);
        $this->hook->attach('dashboard.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a dashboard record
     * @param integer $dashboard_id
     * @return boolean
     */
    public function delete($dashboard_id)
    {
        $result = null;
        $this->hook->attach('dashboard.delete.before', $dashboard_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('dashboard', array('dashboard_id' => $dashboard_id));
        $this->hook->attach('dashboard.delete.after', $dashboard_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a dashboard
     * @param integer $dashboard_id
     * @param array $data
     * @return boolean
     */
    public function update($dashboard_id, array $data)
    {
        $result = null;
        $this->hook->attach('dashboard.update.before', $dashboard_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('dashboard', $data, array('dashboard_id' => $dashboard_id));
        $this->hook->attach('dashboard.update.after', $dashboard_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Add/update a dashboard record for a user
     * @param integer $user_id
     * @param array $data
     * @return bool|integer
     */
    public function set($user_id, array $data)
    {
        $existing = $this->getList(array('user_id' => $user_id));

        if (isset($existing['dashboard_id'])) {
            return $this->update($existing['dashboard_id'], array('data' => $data));
        }

        return $this->add(array('user_id' => $user_id, 'data' => $data));
    }

    /**
     * Returns an array of dashboard handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static('dashboard.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = (array) gplcart_config_get(GC_FILE_CONFIG_DASHBOARD);
        $this->hook->attach('dashboard.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Call a dashboard handler
     * @param string $handler_id
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function callHandler($handler_id, $method = 'data', array $arguments = array())
    {
        try {
            $handlers = $this->getHandlers();
            return Handler::call($handlers, $handler_id, $method, $arguments);
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * Prepare an array of dashboard items
     * @param array $result
     * @param array $options
     * @return array
     */
    protected function prepareList($result, array $options)
    {
        $handlers = $this->getHandlers();

        if (empty($result['data'])) {
            $result['data'] = $handlers;
        } else {
            $result['data'] = array_replace_recursive($handlers, $result['data']);
        }

        foreach ($result['data'] as $handler_id => &$handler) {

            if (!empty($options['active']) && empty($handler['status'])) {
                unset($result['data'][$handler_id]);
                continue;
            }

            $handler['data'] = (array) $this->callHandler($handler_id);
        }

        gplcart_array_sort($result['data']);
        return $result;
    }

}
