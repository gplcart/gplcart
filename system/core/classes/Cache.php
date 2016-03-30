<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\classes;

class Cache
{

    /**
     * Returns a data from the cache file
     * @param string $key
     * @param mixed $default
     * @param integer $lifespan
     * @return mixed
     */
    public static function get($key, $default = null, $lifespan = 0)
    {
        $file = GC_CACHE_DIR . "/$key.cache";

        if (file_exists($file)) {
            $is_fresh = $lifespan ? (filemtime($file) > (GC_TIME - $lifespan)) : true;

            if ($is_fresh) {
                $content = file_get_contents($file);
            }

            if (!empty($content)) {
                $cache = unserialize($content);
            }
        }

        return isset($cache) ? $cache : $default;
    }

    /**
     * Saves a data to the cache file
     * @param string $key
     * @param array $data
     * @return boolean
     */
    public static function set($key, array $data)
    {
        $file = GC_CACHE_DIR . "/$key.cache";
        if (file_put_contents($file, serialize((array) $data)) !== false) {
            chmod($file, 0600); // Read and write for owner, nothing for everybody else
            return true;
        }
        return false;
    }

    /**
     * Removes a cache file
     * @param string $cid
     * @param string $pattern
     * @return integer
     */
    public static function clear($cid, $pattern = '.cache')
    {
        static::clearMemory($cid);

        if ($cid === true) {
            return array_map('unlink', glob(GC_CACHE_DIR . '/*.cache'));
        }
        return array_map('unlink', glob(GC_CACHE_DIR . "/$cid$pattern"));
    }

    /**
     * Deletes a variable from the static storage
     * @param string|null $name
     */
    public static function clearMemory($name = null)
    {
        static::memory($name, null, true);
    }

    /**
     * Central static variable storage
     * @param string $name
     * @param mixed $default_value
     * @param boolean $reset
     * @return mixed
     */
    public static function &memory($name, $default_value = null, $reset = false)
    {
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

}
