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
     * Cache file modification time
     * @var integer
     */
    protected $filemtime;

    /**
     * Cache file path
     * @var string
     */
    protected $file;

    /**
     * Return cache file modification time
     * @return integer
     */
    public function getFileMtime()
    {
        return $this->filemtime;
    }

    /**
     * Returns path to a cache file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets a cache data
     * @param string|array $cid
     * @param mixed $data
     * @return boolean
     */
    public function set($cid, $data)
    {
        $key = gplcart_cache_key($cid);
        $this->file = GC_CACHE_DIR . "/$key.cache";

        if (file_put_contents($this->file, serialize((array) $data)) !== false) {
            chmod($this->file, 0600);
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
        $options += array('default' => null, 'lifespan' => 0);

        $key = gplcart_cache_key($cid);
        $this->file = GC_CACHE_DIR . "/$key.cache";

        if (!file_exists($this->file)) {
            return $options['default'];
        }

        $this->filemtime = filemtime($this->file);

        $fresh = true;
        if (!empty($options['lifespan'])) {
            $fresh = ($this->filemtime > (GC_TIME - $options['lifespan']));
        }

        if (!$fresh) {
            return $options['default'];
        }

        return unserialize(file_get_contents($this->file));
    }

    /**
     * Clears a cached data
     * @param string|null|array $cid
     * @param array $options
     */
    public function clear($cid, $options = array())
    {
        $options += array('pattern' => '.cache');

        gplcart_static_clear($cid);

        if ($cid === null) {
            array_map('unlink', glob(GC_CACHE_DIR . '/*.cache'));
        }

        $key = gplcart_cache_key($cid);
        array_map('unlink', glob(GC_CACHE_DIR . "/$key{$options['pattern']}"));
    }

}
