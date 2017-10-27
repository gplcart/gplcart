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
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Cache file path
     * @var string
     */
    protected $file;

    /**
     * Cache file modification time
     * @var integer
     */
    protected $filemtime;

    /**
     * @param Hook $hook
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Return the cache file modification time
     * @return integer
     */
    public function getFileMtime()
    {
        return $this->filemtime;
    }

    /**
     * Returns path to the current cache file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set cache
     * @param string|array $cid
     * @param mixed $data
     * @return boolean
     */
    public function set($cid, $data)
    {
        $result = null;
        $this->hook->attach('cache.set.before', $cid, $data, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (is_array($cid)) {
            $cid = gplcart_array_hash($cid);
        }

        $this->file = GC_DIR_CACHE . "/$cid.cache";

        $result = false;
        if (file_put_contents($this->file, serialize((array) $data)) !== false) {
            chmod($this->file, 0600);
            $result = true;
        }

        $this->hook->attach('cache.set.after', $cid, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns a cached data
     * @param string|array $cid
     * @param array $options
     * @return mixed
     */
    public function get($cid, $options = array())
    {
        $result = null;
        $options += array('default' => null, 'lifespan' => 0);
        $this->hook->attach('cache.get.before', $cid, $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (is_array($cid)) {
            $cid = gplcart_array_hash($cid);
        }

        $this->file = GC_DIR_CACHE . "/$cid.cache";

        $result = $options['default'];

        if (is_file($this->file)) {
            $this->filemtime = filemtime($this->file);
            if (empty($options['lifespan']) || ((GC_TIME - $this->filemtime) < $options['lifespan'])) {
                $result = unserialize(file_get_contents($this->file));
            }
        }

        $this->hook->attach('cache.get.after', $cid, $options, $result, $this);
        return $result;
    }

    /**
     * Clears a cached data
     * @param string|null|array $cache_id
     * @param array $options
     * @return bool
     */
    public function clear($cache_id, $options = array())
    {
        $result = null;
        $options += array('pattern' => '.cache');
        $this->hook->attach('cache.clear.before', $cache_id, $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = true;

        if ($cache_id === null) {
            array_map('unlink', glob(GC_DIR_CACHE . '/*.cache'));
            gplcart_static_clear();
        } else {

            if (is_array($cache_id)) {
                $cache_id = gplcart_array_hash($cache_id);
            }

            array_map('unlink', glob(GC_DIR_CACHE . "/$cache_id{$options['pattern']}"));
            gplcart_static_clear($cache_id);
        }

        $this->hook->attach('cache.clear.after', $cache_id, $options, $result, $this);
        return (bool) $result;
    }

}
