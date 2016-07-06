<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\classes;

use core\exceptions\SystemLogicalUserAccess;

/**
 * Provides methods to work with sessions
 */
class Session
{

    /**
     * Starts a session
     * @throws SystemLogicalUserAccess
     */
    public function __construct()
    {
        if (!$this->started() && !session_start()) {
            throw new SystemLogicalUserAccess('Failed to start the session');
        }
    }

    /**
     * Regenerates the current session
     * @param boolean $delete_old_session
     * @return boolean
     */
    public function regenerate($delete_old_session)
    {
        if ($this->started()) {
            return session_regenerate_id($delete_old_session);
        }

        return true;
    }

    /**
     * Sets a message to be displayed to the user
     * @param string $message
     * @param string $type
     * @return boolean
     */
    public function setMessage($message, $type = 'info')
    {
        if ($message === '') {
            return false;
        }

        $messages = (array) $this->get('messages', $type, array());

        if (in_array($message, $messages)) {
            return false;
        }

        $messages[] = $message;
        return $this->set('messages', $type, $messages);
    }

    /**
     * Returns a session data
     * @param string $key
     * @param string $secondkey
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $secondkey = null, $default = null)
    {
        if (isset($secondkey)) {
            if (isset($_SESSION[GC_SESSION_PREFIX . $key][$secondkey])) {
                return $_SESSION[GC_SESSION_PREFIX . $key][$secondkey];
            }
        } else {
            if (isset($_SESSION[GC_SESSION_PREFIX . $key])) {
                return $_SESSION[GC_SESSION_PREFIX . $key];
            }
        }

        return $default;
    }

    /**
     * Saves/updates a data in the session
     * @param string $key
     * @param string $secondkey
     * @param mixed $value
     * @return boolean
     */
    public function set($key, $secondkey = null, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[GC_SESSION_PREFIX . $k] = $v;
            }
            return true;
        }

        if (isset($secondkey)) {
            $_SESSION[GC_SESSION_PREFIX . $key][$secondkey] = $value;
            return true;
        }

        $_SESSION[GC_SESSION_PREFIX . $key] = $value;
        return true;
    }

    /**
     * Returns messages from the session
     * @param string $type
     * @return string
     */
    public function getMessage($type = null)
    {
        $message = $this->get('messages', $type, array());
        $this->delete('messages', $type);
        return $message;
    }

    /**
     * Deletes a data from the session
     * @param string $key
     * @param string $secondkey
     * @return boolean
     */
    public function delete($key = null, $secondkey = null)
    {
        if (!$this->started()) {
            return false;
        }

        if (!isset($key)) {
            session_unset();
            return session_destroy();
        }

        if (isset($secondkey)) {
            unset($_SESSION[GC_SESSION_PREFIX . $key][$secondkey]);
            return true;
        }

        unset($_SESSION[GC_SESSION_PREFIX . $key]);
        return true;
    }

    /**
     * Sets/gets the session token
     * @param mixed $value
     * @return mixed
     */
    public function token($value = null)
    {
        if (isset($value)) {
            return $this->set('token', null, $value);
        }

        return $this->get('token');
    }

    /**
     * Returns the current session status
     * @return bool
     */
    protected function started()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return (session_status() === PHP_SESSION_ACTIVE);
        }

        return (session_id() !== '');
    }
}
