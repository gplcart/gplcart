<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

/**
 * Cache system
 */
class Cache
{

    /**
     * Sets a cache data
     * @param string $key
     * @param mixed $data
     * @return boolean
     */
    public function set($key, $data)
    {
        $file = GC_CACHE_DIR . "/$key.cache";

        if (file_put_contents($file, serialize((array) $data)) !== false) {
            chmod($file, 0600);
            return true;
        }

        return false;
    }

    /**
     * Returns a cached data
     * @param string $key
     * @param array $options
     * @return mixed
     */
    public function get($key, $options = array())
    {
        $options += array('default' => null, 'lifespan' => 0);

        $file = GC_CACHE_DIR . "/$key.cache";

        if (!file_exists($file)) {
            return $options['default'];
        }

        $fresh = true;
        if (!empty($options['lifespan'])) {
            $fresh = (filemtime($file) > (GC_TIME - $options['lifespan']));
        }

        if (!$fresh) {
            return $options['default'];
        }

        return unserialize(file_get_contents($file));
    }

    /**
     * Clears a cached data
     * @param string $key
     * @param array $options
     * @return boolean
     */
    public function clear($key, $options = array())
    {
        $options += array('pattern' => '.cache');

        // Clear also memory cache
        static::clearMemory($key);

        if ($key === null) {
            array_map('unlink', glob(GC_CACHE_DIR . '/*.cache'));
            return true;
        }

        array_map('unlink', glob(GC_CACHE_DIR . "/$key{$options['pattern']}"));
        return true;
    }

    /**
     * Central static variable storage
     * Taken from Drupal
     * @param string $name
     * @param mixed $default_value
     * @param boolean $reset
     * @return mixed
     */
    public static function &memory($name, $default_value = null, $reset = false)
    {
        if (is_array($name)) {
            list($key, $hash) = each($name);
            ksort($hash);
            $name = $key . md5(json_encode($hash));
        }

        static $data = array(), $default = array();

        if (isset($data[$name]) || array_key_exists($name, $data)) {
            if ($reset) {
                $data[$name] = $default[$name];
            }

            return $data[$name];
        }

        if (isset($name)) {
            if ($reset) {
                return $data;
            }

            $default[$name] = $data[$name] = $default_value;
            return $data[$name];
        }

        foreach ($default as $name => $value) {
            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * Clears memory cache
     * @param string|null $key
     */
    public static function clearMemory($key = null)
    {
        static::memory($key, null, true);
    }

}
