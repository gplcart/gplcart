<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use RuntimeException;
use gplcart\core\Hook,
    gplcart\core\Cache;
use gplcart\core\helpers\Graph as GraphHelper;

/**
 * Provides methods to work with 3-d party libraries
 */
class Library
{

    use \gplcart\core\traits\Dependency;

    /**
     * Cache instance
     * @var \gplcart\core\Cache $cache
     */
    protected $cache;

    /**
     * Hook instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Graph helper class instance
     * @var \gplcart\core\helpers\Graph $graph
     */
    protected $graph;

    /**
     * Array of loaded libraries
     * @var array
     */
    protected $loaded = array();

    /**
     * Path to default library config file
     * @var string
     */
    protected $default_config;

    /**
     * Constructor
     * @param Cache $cache
     * @param Hook $hook
     * @param GraphHelper $graph
     */
    public function __construct(Cache $cache, Hook $hook, GraphHelper $graph)
    {
        $this->hook = $hook;
        $this->cache = $cache;
        $this->graph = $graph;

        $required_vendor = GC_VENDOR_NAME;
        $this->default_config = GC_VENDOR_DIR . "/$required_vendor/" . GC_VENDOR_CONFIG;

        if (!is_readable($this->default_config)) {
            $this->clearCache();
            $message = "Required library $required_vendor not found."
                    . " Did you install it from https://github.com/$required_vendor?"
                    . " See INSTALL.txt for details.";
            throw new RuntimeException($message);
        }
    }

    /**
     * Returns an array of a library
     * @param string $library_id
     * @return array
     */
    public function get($library_id)
    {
        $libraries = $this->getList();

        if (empty($libraries[$library_id])) {
            return array();
        }

        return $libraries[$library_id];
    }

    /**
     * Returns an array of libraries
     * @return array
     */
    public function getList()
    {
        $libraries = &Cache::memory(__METHOD__);

        if (isset($libraries)) {
            return $libraries;
        }

        $cached = $this->cache->get('libraries');

        if (!empty($cached)) {
            $libraries = $cached;
            return $libraries;
        }

        $libraries = $this->getJsonData($this->default_config);
        $this->hook->fire('library.list', $libraries);

        $libraries = $this->prepareList($libraries);
        $this->cache->set('libraries', $libraries);
        return $libraries;
    }

    /**
     * Removes cached libraries
     */
    public function clearCache()
    {
        $this->cache->clear('libraries');
    }

    /**
     * Validate/filter an array of libraries
     * @param array $libraries
     * @return array
     */
    protected function prepareList(array $libraries)
    {
        foreach ($libraries as $library_id => &$library) {

            $library['id'] = $library_id;

            if (!empty($library['module']) && empty($library['basepath'])) {
                $library['basepath'] = GC_MODULE_DIR . "/{$library['module']}";
            }

            if (empty($library['basepath'])) {
                $library['basepath'] = $this->getDefaultBasePath($library);
            }

            if (!isset($library['version'])) {
                $library['version'] = $this->getVersion($library);
            }

            if (!isset($library['version'])) {
                $library['errors'][] = array('Unknown version', array());
            }

            if (!$this->validateFiles($library)) {
                $library['errors'][] = array('Missing files', array());
            }
        }

        $this->validateDependenciesTrait($libraries);
        $prepared = $this->graph->build($libraries);
        gplcart_array_sort($prepared);
        return $prepared;
    }

    /**
     * Validates libarary files
     * @param array $library
     * @return bool
     */
    protected function validateFiles(array $library)
    {
        if (empty($library['files'])) {
            return false;
        }

        $readable = 0;
        foreach ($library['files'] as $file) {
            $readable += (int) is_readable($library['basepath'] . "/$file");
        }

        return count($library['files']) == $readable;
    }

    /**
     * Parses either .json file or source code
     * and returns a version number for the library
     * @param array $library
     * @return null|string
     */
    public function getVersion(array $library)
    {
        if (isset($library['version'])) {
            return $library['version'];
        }

        if (empty($library['version_source']['file'])) {
            return null;
        }

        $file = "{$library['basepath']}/{$library['version_source']['file']}";

        if (!is_readable($file)) {
            return null;
        }

        // First check if there's a .json file, e.g bower.json
        $version = $this->getVersionJson($file);

        if (isset($version)) {
            return $version;
        }

        return $this->getVersionSource($file, $library);
    }

    /**
     * Returns a base path to a library
     * @param array $library
     * @return string
     */
    protected function getDefaultBasePath(array $library)
    {
        $base = GC_VENDOR_DIR_NAME . '/' . GC_VENDOR_NAME . "/{$library['type']}/{$library['id']}";
        return gplcart_absolute_path($base);
    }

    /**
     * Search for version string in the source code using a regexp pattern
     * @param string $file
     * @param array $library
     * @return null|string
     */
    protected function getVersionSource($file, array $library)
    {
        $library['version_source'] += array(
            'pattern' => '',
            'lines' => 20,
            'cols' => 200,
        );

        $handle = fopen($file, 'r');

        while ($library['version_source']['lines'] && $line = fgets($handle, $library['version_source']['cols'])) {
            $version = array();
            if (preg_match($library['version_source']['pattern'], $line, $version)) {
                fclose($handle);
                return preg_replace('/^[\D\\s]+/', '', $version[1]);
            }

            $library['version_source']['lines'] --;
        }

        fclose($handle);
        return null;
    }

    /**
     * Extracts a version string from .json files
     * @param string $file
     * @return null|string
     */
    public function getVersionJson($file)
    {
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
            return null;
        }

        $data = $this->getJsonData($file);

        if (empty($data)) {
            return null;
        }

        if (isset($data['version'])) {
            return preg_replace('/^[\D\\s]+/', '', $data['version']);
        }

        return null;
    }

    /**
     * Extracts an array of data from a .json file
     * @param string $file
     * @return array
     */
    protected function getJsonData($file)
    {
        $json = file_get_contents($file);

        if ($json === false) {
            return array();
        }

        $data = json_decode(trim($json), true);
        return empty($data) ? array() : (array) $data;
    }

    /**
     * Returns an array of sorted files for the given library IDs
     * according to their dependencies
     * @param string|array $ids
     * @return array
     */
    public function getFiles($ids)
    {
        settype($ids, 'array');

        $libraries = $this->getList();
        $sorted = $this->graph->sort($ids, $libraries);

        if (empty($sorted)) {
            return array();
        }

        return $this->prepareFiles($sorted, $libraries);
    }

    /**
     * Includes libraries files
     * @param array|string $ids
     * @return boolean
     */
    public function load($ids)
    {
        settype($ids, 'array');

        $libraries = $this->getList();

        foreach ($ids as $key => $id) {

            if ($this->isLoaded($id)) {
                unset($ids[$key]);
                continue;
            }

            if (empty($libraries[$id]['type'])) {
                unset($ids[$key]);
                continue;
            }

            if ($libraries[$id]['type'] !== 'php') {
                unset($ids[$key]);
            }
        }

        $sorted = $this->graph->sort($ids, $libraries);

        if (empty($sorted)) {
            return false;
        }

        foreach ($this->prepareFiles($sorted, $libraries) as $file) {
            require_once $file;
        }

        $this->loaded = array_merge($this->loaded, $sorted);
        return true;
    }

    /**
     * Returns an array of loaded libraies
     * @return array
     */
    public function getLoaded()
    {
        return $this->loaded;
    }

    /**
     * Whether a given library is already loaded
     * @param string $name
     * @return bool
     */
    public function isLoaded($name)
    {
        return in_array($name, $this->loaded);
    }

    /**
     * Prepares files of given library IDs
     * @param array $ids
     * @param array $libraries
     * @return array
     */
    protected function prepareFiles(array $ids, array $libraries)
    {
        $prepared = array();
        foreach ($ids as $id) {
            $library = $libraries[$id];
            array_walk($library['files'], function(&$file) use($library) {
                $file = "{$library['basepath']}/$file";
            });
            $prepared = array_merge($prepared, $library['files']);
        }

        return $prepared;
    }

}
