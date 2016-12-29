<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Cache;
use core\helpers\Graph as GraphHelper;

/**
 * Provides methods to work with 3-d party libraries
 */
class Library
{

    /**
     * Cache instance
     * @var \core\Cache $cache
     */
    protected $cache;

    /**
     * Graph helper class instance
     * @var \core\helpers\Graph $graph
     */
    protected $graph;

    /**
     * An array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     * @param Cache $cache
     * @param GraphHelper $graph
     */
    public function __construct(Cache $cache, GraphHelper $graph)
    {
        $this->cache = $cache;
        $this->graph = $graph;
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
     * @param bool $cache
     * @return array
     */
    public function getList($cache = true)
    {
        $libraries = &Cache::memory('libraries');

        if (isset($libraries)) {
            return $libraries;
        }

        $cached = $this->cache->get('libraries');

        if ($cache && isset($cached)) {
            $libraries = $cached;
        } else {
            $_libraries = include GC_CONFIG_LIBRARY;
            $libraries = $this->prepareList($_libraries);

            gplcart_array_sort($libraries);
            $this->cache->set('libraries', $libraries);
        }

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
     * Validates/ filter an array of libraries
     * @param array $libraries
     * @return array
     */
    protected function prepareList(array $libraries)
    {
        foreach ($libraries as $library_id => &$library) {

            $library['id'] = $library_id;
            $version = $this->getVersion($library);

            if (!isset($version)) {
                $this->errors[$library_id][] = 'unknown_version';
            }

            $library['version']['number'] = $version;
        }

        foreach ($libraries as $library_id => &$library) {
            $this->checkDependencies($libraries, $library);
        }

        return $this->graph->build($libraries);
    }

    /**
     * Checks library dependencies
     * @param array $libraries
     * @param array $library
     */
    protected function checkDependencies(array $libraries, array $library)
    {
        if (empty($library['dependencies'])) {
            return true;
        }

        foreach ($library['dependencies'] as $library_id => $version) {

            if (!isset($libraries[$library_id])) {
                $this->errors[$library_id][] = 'missing_required';
                continue;
            }

            $components = gplcart_version_components($version);

            if (empty($components)) {
                $this->errors[$library_id][] = 'unknown_version_required';
                continue;
            }

            list($operator, $number) = $components;

            if (!version_compare($libraries[$library_id]['version']['number'], $number, $operator)) {
                $this->errors[$library['id']][] = 'incompatible_version_required';
            }
        }
    }

    /**
     * Parses either .json file or source code
     * and returns a version number for the library
     * @param array $library
     * @return null|string
     */
    public function getVersion(array &$library)
    {
        if (isset($library['version']['number'])) {
            // Some libraries have no version number.
            // In this case the version can be provided in the library definition
            return $library['version']['number'];
        }

        if (empty($library['version']['file'])) {
            return null;
        }

        $file = $this->getVersionFile($library);

        if (empty($file)) {
            $this->errors[$library['id']][] = 'failed_load_file';
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
     * Returns a source file containing verison info
     * @param array $library
     * @return string
     */
    protected function getVersionFile(array &$library)
    {
        if ($library['type'] === 'php') {
            $base = GC_LIBRARY_DIR . "/{$library['id']}";
            $file = "/$base/{$library['version']['file']}";
        } else {
            $base = "files/assets/libraries/{$library['id']}";
            $file = GC_ROOT_DIR . "/$base/{$library['version']['file']}";
        }

        $library['basepath'] = $base;
        return is_readable($file) ? $file : '';
    }

    /**
     * Search for version string in the source code using a regexp pattern
     * @param string $file
     * @param array $library
     * @return null|string
     */
    protected function getVersionSource($file, array $library)
    {
        $library['version'] += array(
            'pattern' => '',
            'lines' => 20,
            'cols' => 200,
        );

        $handle = fopen($file, 'r');

        while ($library['version']['lines'] && $line = fgets($handle, $library['version']['cols'])) {
            if (preg_match($library['version']['pattern'], $line, $version)) {
                fclose($handle);
                // Clean up
                return preg_replace('/^[\D\\s]+/', '', $version[1]);
            }

            $library['version']['lines'] --;
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

        $json = file_get_contents($file);

        if ($json === false) {
            return null;
        }

        $json = trim($json);

        if (empty($json)) {
            return null;
        }

        $data = json_decode($json, true);

        if (isset($data['version'])) {
            // Clean up
            return preg_replace('/^[\D\\s]+/', '', $data['version']);
        }

        return null;
    }

    /**
     * Returns an array of sorted files for the given library IDs
     * according to their dependencies
     * @param string|array $ids
     * @return array
     */
    public function getFiles($ids)
    {
        $libraries = $this->getList();
        $sorted = $this->graph->sort((array) $ids, $libraries);

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
        $libraries = $this->getList();

        $ids = (array) $ids;
        foreach ($ids as $key => $id) {
            if (empty($libraries[$id]['type']) || $libraries[$id]['type'] !== 'php') {
                unset($ids[$key]);
            }
        }

        $sorted = $this->graph->sort($ids, $libraries);

        if (empty($sorted)) {
            return false;
        }

        $prepared = $this->prepareFiles($sorted, $libraries);

        foreach ($prepared as $file) {
            require_once $file;
        }
        
        return true;
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

    /**
     * Returns library validation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
