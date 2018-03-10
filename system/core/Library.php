<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Graph as GraphHelper;
use gplcart\core\traits\Dependency as DependencyTrait;

/**
 * Provides methods to work with 3-d party libraries
 */
class Library
{

    use DependencyTrait;

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
     * @param Hook $hook
     * @param GraphHelper $graph
     */
    public function __construct(Hook $hook, GraphHelper $graph)
    {
        $this->hook = $hook;
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
        return empty($libraries[$library_id]) ? array() : $libraries[$library_id];
    }

    /**
     * Returns an array of libraries
     * @return array
     */
    public function getList()
    {
        $libraries = &gplcart_static('library.list');

        if (isset($libraries)) {
            return (array) $libraries;
        }

        $libraries = (array) gplcart_config_get(GC_FILE_CONFIG_LIBRARY);
        $this->hook->attach('library.list', $libraries, $this);
        $libraries = (array) $this->prepareList($libraries);

        return $libraries;
    }

    /**
     * Validate/filter an array of libraries
     * @param array $libraries
     * @return array
     */
    protected function prepareList(array $libraries)
    {
        foreach ($libraries as $library_id => &$library) {
            $this->prepareListItem($libraries, $library_id, $library);
        }

        $this->validateDependencies($libraries);
        $prepared = $this->graph->build($libraries);
        gplcart_array_sort($prepared);

        return $prepared;
    }

    /**
     * Prepare a library list item
     * @param array $libraries
     * @param string $library_id
     * @param array $library
     * @return null
     */
    protected function prepareListItem(array &$libraries, $library_id, array &$library)
    {
        if (empty($library['type'])) {
            unset($libraries[$library_id]);
            return null;
        }

        $types = $this->getTypes();

        if (empty($types[$library['type']])) {
            unset($libraries[$library_id]);
            return null;
        }

        $library['id'] = $library_id;

        if (!isset($library['basepath'])) {
            if (isset($library['vendor'])) {
                $library['vendor'] = strtolower($library['vendor']);
                $library['basepath'] = GC_DIR_VENDOR . "/{$library['vendor']}";
            } else if (isset($types[$library['type']]['basepath'])) {
                $library['basepath'] = "{$types[$library['type']]['basepath']}/$library_id";
            } else {
                unset($libraries[$library_id]);
                return null;
            }
        }

        if ($library['type'] === 'php' && empty($library['files']) && !empty($library['vendor'])) {
            $library['files'] = array(GC_FILE_AUTOLOAD);
        }

        if (!isset($library['version'])) {
            $library['errors'][] = array('Unknown version', array());
        }

        if (!$this->validateFiles($library)) {
            $library['errors'][] = array('Missing files', array());
        }

        return null;
    }

    /**
     * Validates library files
     * @param array $library
     * @return bool
     */
    protected function validateFiles(array &$library)
    {
        if (empty($library['files'])) {
            return true; // Assume files will be loaded on dynamically
        }

        $readable = $count = 0;

        foreach ($library['files'] as &$file) {

            $count++;

            if (!gplcart_path_is_absolute($file)) {
                $file = $library['basepath'] . "/$file";
            }

            $readable += (int) (is_file($file) && is_readable($file));
        }

        return $count == $readable;
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

        $types = $this->getTypes();
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

            if (empty($types[$libraries[$id]['type']]['load'])) {
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
     * Returns an array of loaded libraries
     * @return array
     */
    public function getLoaded()
    {
        return $this->loaded;
    }

    /**
     * Whether the library is already loaded
     * @param string $name
     * @return bool
     */
    public function isLoaded($name)
    {
        return in_array($name, $this->loaded);
    }

    /**
     * Returns an array of supported library types
     * @return array
     */
    public function getTypes()
    {
        return array(
            'php' => array('load' => true),
            'asset' => array('basepath' => GC_DIR_VENDOR_ASSET)
        );
    }

    /**
     * Prepares files of the given libraries
     * @param array $ids
     * @param array $libraries
     * @return array
     */
    protected function prepareFiles(array $ids, array $libraries)
    {
        $prepared = array();

        foreach ($ids as $id) {
            if (!empty($libraries[$id]['files'])) {
                $prepared = array_merge($prepared, $libraries[$id]['files']);
            }
        }

        return $prepared;
    }

}
