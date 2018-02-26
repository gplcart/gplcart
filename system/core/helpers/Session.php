<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use RuntimeException;

/**
 * Provides methods to work with sessions
 */
class Session
{

    /**
     * Initialize a new session
     * @throws RuntimeException
     */
    public function init()
    {
        if (!GC_CLI && !$this->isInitialized() && !session_start()) {
            throw new RuntimeException('Failed to start the session');
        }
    }

    /**
     * Returns the current session status
     * @return bool
     */
    public function isInitialized()
    {
        return !GC_CLI && session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Regenerates the current session
     * @param bool $delete_old_session
     * @throws RuntimeException
     */
    public function regenerate($delete_old_session)
    {
        if (!session_regenerate_id($delete_old_session)) {
            throw new RuntimeException('Failed to regenerate the current session');
        }
    }

    /**
     * Returns a session data
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!GC_CLI && isset($_SESSION)) {
            $value = gplcart_array_get($_SESSION, $key);
        }

        return isset($value) ? $value : $default;
    }

    /**
     * Set a session data
     * @param string|array $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value = null)
    {
        if (!GC_CLI && isset($_SESSION)) {
            gplcart_array_set($_SESSION, $key, $value);
            return true;
        }

        return false;
    }

    /**
     * Deletes a data from the session
     * @param mixed $key
     * @return bool
     * @throws RuntimeException
     */
    public function delete($key = null)
    {
        if (!$this->isInitialized()) {
            return false;
        }

        if (!isset($key)) {

            session_unset();

            if (!session_destroy()) {
                throw new RuntimeException('Failed to delete the session');
            }

            return true;
        }

        gplcart_array_unset($_SESSION, $key);
        return true;
    }

    /**
     * Sets a message to be displayed to the user
     * @param string $message
     * @param string $type
     * @param string $key
     */
    public function setMessage($message, $type = 'info', $key = 'messages')
    {
        if ($message !== '') {

            $messages = (array) $this->get("$key.$type", array());

            if (!in_array($message, $messages)) {
                $messages[] = $message;
                $this->set("$key.$type", $messages);
            }
        }
    }

    /**
     * Returns messages from the session
     * @param string $type
     * @param string $key
     * @return string|array
     */
    public function getMessage($type = null, $key = 'messages')
    {
        if (isset($type)) {
            $key .= ".$type";
        }

        $message = $this->get($key, array());
        $this->delete($key);
        return $message;
    }

}
