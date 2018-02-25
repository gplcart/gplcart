<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

/**
 * Contains methods to work with system configurations
 */
class Config
{

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Private system key
     * @var string
     */
    protected $key;

    /**
     * An array of configuration
     * @var array
     */
    protected $config = array();

    /**
     * Whether the configuration initialized
     * @var boolean
     */
    protected $initialized;

    /**
     * @param Logger $logger
     * @param Database $db
     */
    public function __construct(Logger $logger, Database $db)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Initialize the system configuration
     * @param string $file
     * @return bool
     */
    public function init($file = GC_FILE_CONFIG_COMPILED)
    {
        $this->initialized = false;

        $this->setErrorHandlers();

        if (is_file($file)) {
            $this->setConfig($file);
            $this->setErrorLevel();
            $this->setKey();
            $this->setLogger();
            date_default_timezone_set($this->get('timezone', 'Europe/London'));
            $this->initialized = true;
        }

        return $this->initialized;
    }

    /**
     * Set PHP error reporting level
     */
    protected function setErrorLevel()
    {
        switch ($this->get('error_level', 2)) {
            case 0:
                error_reporting(0);
                break;
            case 1:
                error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
                break;
            case 2:
                error_reporting(E_ALL);
                break;
        }
    }

    /**
     * Sets PHP error handlers
     */
    protected function setErrorHandlers()
    {
        register_shutdown_function(array($this->logger, 'shutdownHandler'));
        set_exception_handler(array($this->logger, 'exceptionHandler'));
        set_error_handler(array($this->logger, 'errorHandler'), error_reporting());
    }

    /**
     * Sets logger
     */
    protected function setLogger()
    {
        $this->logger->setDb($this->db)
            ->printError($this->get('error_print', false))
            ->logBacktrace($this->get('error_log_backtrace', true))
            ->printBacktrace($this->get('error_print_backtrace', false))
            ->errorToException($this->get('error_to_exception', false));
    }

    /**
     * Set array of configuration options
     * @param array|string $config Either a file path or an array of configuration options
     */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $this->config = $config;
        } else {
            $this->config = (array) gplcart_config_get((string) $config);
            $this->setDb($this->config['database']);
            $this->config = array_merge($this->config, $this->select());
        }
    }

    /**
     * Sets the private key
     * @param null|string $key
     * @return string
     */
    public function setKey($key = null)
    {
        if (empty($key)) {

            $key = $this->get('private_key');

            if (empty($key)) {
                $key = gplcart_string_random();
                $this->set('private_key', $key);
            }

        } else {
            $this->set('private_key', $key);
        }

        return $this->key = $key;
    }

    /**
     * Sets the database
     * @param mixed $db
     * @return \gplcart\core\Database
     */
    public function setDb($db)
    {
        return $this->db->init($db);
    }

    /**
     * Returns a value from an array of configuration options
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (!isset($key)) {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Saves a configuration value in the database
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function set($key, $value)
    {
        if (!$this->db->isInitialized() || empty($key) || !isset($value)) {
            return false;
        }

        $this->reset($key);

        $serialized = 0;

        if (is_array($value)) {
            $value = serialize($value);
            $serialized = 1;
        }

        $values = array(
            'id' => $key,
            'value' => $value,
            'created' => GC_TIME,
            'serialized' => $serialized
        );

        $this->db->insert('settings', $values);
        return true;
    }

    /**
     * Select all or a single setting from the database
     * @param null|string $name
     * @param mixed $default
     * @return mixed
     */
    public function select($name = null, $default = null)
    {
        if (!$this->db->isInitialized()) {
            return isset($name) ? $default : array();
        }

        $conditions = array();

        $sql = 'SELECT * FROM settings';

        if (isset($name)) {
            $conditions[] = $name;
            $sql .= ' WHERE id=? LIMIT 0,1';
        }

        $settings = array();

        foreach ($this->db->fetchAll($sql, $conditions) as $result) {

            if (!empty($result['serialized'])) {
                $result['value'] = unserialize($result['value']);
            }

            $settings[$result['id']] = $result['value'];
        }

        return isset($name) ? reset($settings) : $settings;
    }

    /**
     * Deletes a setting from the database
     * @param string $key
     * @return boolean
     */
    public function reset($key)
    {
        return (bool) $this->db->delete('settings', array('id' => $key));
    }

    /**
     * Whether the configuration initialized
     * @return boolean
     */
    public function isInitialized()
    {
        return (bool) $this->initialized;
    }

    /**
     * Returns the database class instance
     * @return \gplcart\core\Database
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns the private key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}
