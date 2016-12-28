<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\helpers\Graph as GraphHelper;
use core\models\Cache as CacheModel;
use core\models\Language as LanguageModel;

/**
 * Provides methods to work with 3-d party libraries
 */
class Library extends Model
{

    /**
     * An array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Graph helper class instance
     * @var \core\helpers\Graph $graph
     */
    protected $graph;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Cache model instance
     * @var \core\models\Cache $cache
     */
    protected $cache;

    /**
     * Constructor
     * @param CacheModel $cache
     * @param LanguageModel $language
     * @param GraphHelper $graph
     */
    public function __construct(CacheModel $cache, LanguageModel $language,
            GraphHelper $graph)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->graph = $graph;
        $this->language = $language;
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
        $libraries = &gplcart_cache('libraries');

        if (isset($libraries)) {
            return $libraries;
        }

        $cached = $this->cache->get('libraries');

        if ($cache && isset($cached)) {
            $libraries = $cached;
        } else {
            $_libraries = include_once GC_CONFIG_LIBRARY;
            $libraries = $this->prepareList($_libraries);

            gplcart_array_sort($libraries);

            $cached = $this->cache->set('libraries', $libraries);
        }

        $this->hook->fire('libraries', $libraries);
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
                $vars = array('%name' => $library['name']);
                $error = $this->language->text('Unknown version for library %name', $vars);
                $this->errors[$library_id][] = $error;
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
     * @return boolean
     */
    protected function checkDependencies(array $libraries, array $library)
    {
        if (empty($library['dependencies'])) {
            return true;
        }

        foreach ($library['dependencies'] as $library_id => $version) {

            if (!isset($libraries[$library_id])) {
                $vars = array('%id' => $library_id);
                $error = $this->language->text('Missing required library %id', $vars);
                $this->errors[$library_id][] = $error;
                continue;
            }

            $components = gplcart_version_components($version);

            if (empty($components)) {
                $vars = array('%version' => $version, '%name' => $libraries[$library_id]['name']);
                $error = $this->language->text('Unknown version %version for required library %name', $vars);
                $this->errors[$library_id][] = $error;
                continue;
            }

            list($operator, $number) = $components;

            if (!version_compare($libraries[$library_id]['version']['number'], $number, $operator)) {
                $vars = array('%required' => $libraries[$library_id]['name'], '%dependent' => $library['name']);
                $error = $this->language->text('Required library %required is not compatible with %dependent', $vars);
                $this->errors[$library['id']][] = $error;
            }
        }

        return !isset($error);
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
            $vars = array('%path' => $file);
            $error = $this->language->text('Failed to load file %path', $vars);
            $this->errors[$library['id']][] = $error;
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
        $base = $library['type'] == 'php' ? 'system/libraries' : 'files/assets/libraries';
        $file = GC_ROOT_DIR . "/$base/{$library['id']}/{$library['version']['file']}";
        $library['basepath'] = "$base/{$library['id']}";

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
     * Returns an array of sorted files for the given library IDs
     * according to their dependencies
     * @param string|array $ids
     * @return array
     */
    public function getFiles($ids)
    {
        $libraries = $this->getList();

        // Sort library IDs according to their dependencies
        // Dependent libraries always go last
        $sorted = $this->graph->sort((array) $ids, $libraries);

        if (empty($sorted)) {
            return array();
        }

        // Merge into groups, prepare file URLs
        return $this->prepareFiles($sorted, $libraries);
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
     * Returns library validation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
