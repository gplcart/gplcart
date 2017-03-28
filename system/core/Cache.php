<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

/**
 * Cache system
 */
class Cache
{

    /**
     * Sets a cache data
     * @param string|array $cid
     * @param mixed $data
     * @return boolean
     */
    public function set($cid, $data)
    {
        $key = static::buildKey($cid);
        $file = GC_CACHE_DIR . "/$key.cache";

        if (file_put_contents($file, serialize((array) $data)) !== false) {
            chmod($file, 0600);
            return true;
        }

        return false;
    }

    /**
     * Returns a cached data
     * @param string|array $cid
     * @param array $options
     * @return mixed
     */
    public function get($cid, $options = array())
    {
        $key = static::buildKey($cid);
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
     * @param string|null|array $cid
     * @param array $options
     * @return boolean
     */
    public function clear($cid, $options = array())
    {
        $options += array('pattern' => '.cache');

        // Clear also memory cache
        static::clearMemory($cid);

        if ($cid === null) {
            array_map('unlink', glob(GC_CACHE_DIR . '/*.cache'));
            return true;
        }

        $key = static::buildKey($cid);
        array_map('unlink', glob(GC_CACHE_DIR . "/$key{$options['pattern']}"));
        return true;
    }

    /**
     * Central static variable storage
     * Taken from Drupal
     * @param string|null|array $cid
     * @param mixed $default_value
     * @param boolean $reset
     * @return mixed
     */
    public static function &memory($cid, $default_value = null, $reset = false)
    {
        $name = static::buildKey($cid);

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
     * Generates a cache key from an array of arguments like ('prefix' => array(...))
     * @param string|array|null $data
     * @return string|null
     */
    protected static function buildKey($data)
    {
        if ($data === null) {
            return null;
        }

        if (is_array($data)) {
            list($key, $hash) = each($data);
            ksort($hash);
            $data = md5($key . json_encode($hash));
        }

        return $data;
    }

    /**
     * Clears memory cache
     * @param mixed $cid
     */
    public static function clearMemory($cid = null)
    {
        $key = static::buildKey($cid);
        static::memory($key, null, true);
    }

}
