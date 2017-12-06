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

        if (is_file($file)) {
            $this->config = (array) gplcart_config_get($file);
            $this->setDb($this->config['database']);
            $this->config = array_merge($this->config, $this->select());
            $this->setKey();
            $this->setEnvironment();
            $this->initialized = true;
        }

        return $this->initialized;
    }

    /**
     * Configure environment
     */
    protected function setEnvironment()
    {
        $level = $this->get('error_level', 2);

        if ($level == 0) {
            error_reporting(0);
        } else if ($level == 1) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        } else if ($level == 2) {
            error_reporting(E_ALL);
        }

        $this->logger->setDb($this->db);

        register_shutdown_function(array($this->logger, 'shutdownHandler'));
        set_exception_handler(array($this->logger, 'exceptionHandler'));
        set_error_handler(array($this->logger, 'errorHandler'), error_reporting());

        date_default_timezone_set($this->get('timezone', 'Europe/London'));
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
        return $this->db->set($db);
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

        if (isset($name)) {
            return $this->selectOne($name, $default);
        }

        return $this->selectAll();
    }

    /**
     * Select a single setting from the database
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function selectOne($name, $default = null)
    {
        $result = $this->db->fetch('SELECT * FROM settings WHERE id=?', array($name));

        if (empty($result)) {
            return $default;
        }

        if (!empty($result['serialized'])) {
            return unserialize($result['value']);
        }

        return $result['value'];
    }

    /**
     * Select all settings from the database
     * @return array
     */
    protected function selectAll()
    {
        $results = $this->db->fetchAll('SELECT * FROM settings', array());

        $settings = array();
        foreach ($results as $result) {
            if (!empty($result['serialized'])) {
                $result['value'] = unserialize($result['value']);
            }
            $settings[$result['id']] = $result['value'];
        }

        return $settings;
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
