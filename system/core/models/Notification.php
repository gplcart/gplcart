<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Hook;
use core\Config;
use core\Handler;
use core\classes\Tool;
use core\classes\Cache;

/**
 * Manages basic behaviors and data related to various site notifications
 */
class Notification
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
    }

    /**
     * Sets a notification
     * @param string $notification_id
     * @param array $arguments
     * @return mixed
     */
    public function set($notification_id, array $arguments = array())
    {
        $handlers = $this->getHandlers();

        if (empty($handlers[$notification_id])) {
            return false;
        }

        $handler = $handlers[$notification_id];

        if (isset($handler['storage']) && $handler['storage'] == 'database') {
            $database = true;
            $this->clear($notification_id);
        }

        $result = Handler::call($handlers, $notification_id, 'process', $arguments);

        if (isset($database)) {
            $this->save($notification_id, $result);
        }

        return $result;
    }

    /**
     * Returns an array of handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory('notification.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['system_status'] = array(
            'storage' => 'database',
            'access' => 'admin',
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Common', 'system'),
            ),
        );

        $handlers['order_status'] = array(
            'access' => 'order',
            'storage' => 'database',
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Order', 'status'),
            ),
        );

        // Order submitted and created, goes to admins
        $handlers['order_created_admin'] = array(
            'access' => 'order',
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Order', 'createdAdmin')
            ),
        );

        // Order submitted and created by a logged in customer
        $handlers['order_created_customer'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Order', 'createdCustomer'),
            ),
        );

        // Message on the order complete page for a logged in cutomer
        $handlers['order_complete_customer'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Order', 'completeCustomer'),
            ),
        );

        // Message on the order complete page for an anonymous
        $handlers['order_complete_anonymous'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Order', 'completeAnonymous'),
            ),
        );

        $handlers['order_updated_customer'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Order', 'updatedCustomer'),
            ),
        );

        $handlers['user_registered_admin'] = array(
            'access' => 'user',
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Account', 'registeredAdmin'),
            ),
        );

        $handlers['user_registered_customer'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Account', 'registeredCustomer'),
            ),
        );

        $handlers['user_reset_password'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Account', 'resetPassword'),
            ),
        );

        $handlers['user_changed_password'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\notification\\Account', 'changedPassword'),
            ),
        );

        $this->hook->fire('notification.handlers', $handlers);
        return $handlers;
    }

    /**
     * Returns an array of all notifications
     * @param integer $limit
     * @return array
     */
    public function getList($limit = 0)
    {
        $messages = array();
        $handlers = $this->getHandlers();

        foreach ($handlers as $notification_id => $handler) {
            if (isset($handler['storage']) && $handler['storage'] === 'database') {
                $message = $this->get($notification_id);
                $message['access'] = isset($handler['access']) ? $handler['access'] : false;
                $messages[$notification_id] = $message;
            }
        }

        Tool::sortWeight($messages);

        if (!empty($limit)) {
            $messages = array_slice($messages, 0, $limit);
        }

        return $messages;
    }

    /**
     * Returns a notification from the database
     * @param string $notification_id
     * @return array
     */
    public function get($notification_id)
    {
        return $this->config->get("notification_$notification_id", array());
    }

    /**
     * Saves a notification to the database
     * @param string $notification_id
     * @param mixed $data
     * @return boolean
     */
    public function save($notification_id, $data)
    {
        if (empty($data)) {
            return false;
        }

        return $this->config->set("notification_$notification_id", $data);
    }

    /**
     * Removes a notification from the database
     * @param string $notification_id
     * @return boolean
     */
    public function clear($notification_id)
    {
        return $this->config->reset("notification_$notification_id");
    }
}
