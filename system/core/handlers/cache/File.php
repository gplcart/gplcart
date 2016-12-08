<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\cache;

use core\handlers\cache\Base as BaseHandler;

/**
 * Cache handler for file storage
 */
class File extends BaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Caches a data in the file store
     * @param string $key
     * @param mixed $data
     * @param array $options
     * @return boolean
     */
    public function set($key, $data, array $options)
    {
        $file = GC_CACHE_DIR . "/$key.cache";
        if (file_put_contents($file, serialize((array) $data)) !== false) {
            chmod($file, 0600);
            return true;
        }
        return false;
    }

    /**
     * Returns a stored data from the cache
     * @param string $key
     * @param array $options
     * @return mixed
     */
    public function get($key, array $options)
    {
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
     * Clears the cached data
     * @param null|string $key
     * @param array $options
     * @return boolean
     */
    public function clear($key, array $options)
    {
        $options += array('pattern' => '.cache');

        // Clear also memory cache
        gplcart_cache_clear($key);

        if ($key === null) {
            array_map('unlink', glob(GC_CACHE_DIR . '/*.cache'));
            return true;
        }

        array_map('unlink', glob(GC_CACHE_DIR . "/$key{$options['pattern']}"));
        return true;
    }

}
