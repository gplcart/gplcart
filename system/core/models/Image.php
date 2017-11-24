<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Handler;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\File as FileModel;

/**
 * Manages basic behaviors and data related to images
 */
class Image
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file;
     */
    protected $file;

    /**
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param FileModel $file
     * @param UrlHelper $url
     */
    public function __construct(Hook $hook, Config $config, FileModel $file, UrlHelper $url)
    {
        $this->hook = $hook;
        $this->config = $config;

        $this->url = $url;
        $this->file = $file;
    }

    /**
     * Returns an array of image style action handlers
     * @return array
     */
    public function getActionHandlers()
    {
        $handlers = &gplcart_static('imagestyle.action.handlers');

        if (isset($handlers)) {
            return (array) $handlers;
        }

        $handlers = (array) gplcart_config_get(GC_FILE_CONFIG_IMAGE_ACTION);
        $this->hook->attach('imagestyle.action.handlers', $handlers, $this);
        return (array) $handlers;
    }

    /**
     * Returns a single image action handler
     * @param string $action_id
     * @return array
     */
    public function getActionHandler($action_id)
    {
        $actions = $this->getActionHandlers();
        return empty($actions[$action_id]) ? array() : $actions[$action_id];
    }

    /**
     * Apply a single action to an image file
     * @param string $source
     * @param string $target
     * @param array $handler
     * @param array $action
     * @return boolean
     */
    public function processAction(&$source, &$target, $handler, &$action)
    {
        try {
            $callback = Handler::get($handler, null, 'process');
            return call_user_func_array($callback, array(&$source, &$target, &$action));
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Returns a string containing a thumbnail image URL
     * @param array $data
     * @param array $options
     * @return string
     */
    public function getThumb(array $data, array $options)
    {
        $options += array(
            'placeholder' => true,
            'imagestyle' => $this->config->get('image_style', 3));

        if (empty($options['id_value'])) {
            return empty($options['placeholder']) ? '' : $this->getPlaceholder($options['imagestyle']);
        }

        $conditions = array(
            'order' => 'asc',
            'sort' => 'weight',
            'file_type' => 'image',
            'id_key' => $options['id_key'],
            'id_value' => (array) $options['id_value']
        );

        foreach ((array) $this->file->getList($conditions) as $file) {
            if (isset($data[$options['id_key']]) && $file['id_value'] == $data[$options['id_key']]) {
                return $this->url($options['imagestyle'], $file['path']);
            }
        }

        return empty($options['placeholder']) ? '' : $this->getPlaceholder($options['imagestyle']);
    }

    /**
     * Deletes multiple images
     * @param array $options
     * @return bool
     */
    public function deleteMultiple(array $options)
    {
        $deleted = $count = 0;
        foreach ((array) $this->file->getList($options) as $file) {
            $count ++;
            $deleted += (int) $this->file->delete($file['file_id']);
        }

        return $count && $deleted == $count;
    }

    /**
     * Modify an image
     * @param array $actions
     * @param string $source
     * @param string $target
     * @return int
     */
    public function applyActions(array $actions, $source, $target)
    {
        $applied = 0;
        foreach ($actions as $action_id => $data) {
            $handler = $this->getActionHandler($action_id);
            if (!empty($handler)) {
                $applied += (int) $this->processAction($source, $target, $handler, $data);
            }
        }

        return $applied;
    }

    /**
     * Returns an array of image styles
     * @return array
     */
    public function getStyleList()
    {
        $imagestyles = &gplcart_static('imagestyle.list');

        if (isset($imagestyles)) {
            return (array) $imagestyles;
        }

        $default = (array) gplcart_config_get(GC_FILE_CONFIG_IMAGE_STYLE);
        $saved = $this->config->get('imagestyles', array());
        $imagestyles = array_replace_recursive($default, $saved);

        foreach ($imagestyles as $imagestyle_id => &$imagestyle) {
            $imagestyle['imagestyle_id'] = $imagestyle_id;
            $imagestyle['default'] = isset($default[$imagestyle_id]);
            $imagestyle['in_database'] = isset($saved[$imagestyle_id]);
        }

        $this->hook->attach('imagestyle.list', $imagestyles, $this);
        return (array) $imagestyles;
    }

    /**
     * Returns an array of image style actions
     * @param integer $imagestyle_id
     * @return array
     */
    public function getStyleActions($imagestyle_id)
    {
        $styles = $this->getStyleList();

        if (empty($styles[$imagestyle_id]['actions'])) {
            return array();
        }

        $actions = $styles[$imagestyle_id]['actions'];

        gplcart_array_sort($actions);
        return $actions;
    }

    /**
     * Loads an image style
     * @param  integer $imagestyle_id
     * @return array
     */
    public function getStyle($imagestyle_id)
    {
        $imagestyles = $this->getStyleList();
        return isset($imagestyles[$imagestyle_id]) ? $imagestyles[$imagestyle_id] : array();
    }

    /**
     * Adds an image style
     * @param array $data
     * @return integer
     */
    public function addStyle(array $data)
    {
        $result = null;
        $this->hook->attach('imagestyle.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $default = $this->getDefaultData();
        $data += $default;

        $imagestyle_id = count($this->getStyleList()) + 1;
        $imagestyles = $this->config->get('imagestyles', array());

        $imagestyles[$imagestyle_id] = array_intersect_key($data, $default);
        $this->config->set('imagestyles', $imagestyles);

        $this->hook->attach('imagestyle.add.after', $data, $imagestyle_id, $this);
        return (int) $imagestyle_id;
    }

    /**
     * Updates an image style
     * @param integer $imagestyle_id
     * @param array $data
     * @return boolean
     */
    public function updateStyle($imagestyle_id, array $data)
    {
        $result = null;
        $this->hook->attach('imagestyle.update.before', $imagestyle_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $default = $this->getDefaultData();
        $data += $default;

        $imagestyles = $this->config->select('imagestyles', array());
        $imagestyles[$imagestyle_id] = array_intersect_key($data, $default);
        $this->config->set('imagestyles', $imagestyles);

        $result = true;
        $this->hook->attach('imagestyle.update.after', $imagestyle_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes an image style
     * @param integer $imagestyle_id
     * @param bool $check
     * @return boolean
     */
    public function deleteStyle($imagestyle_id, $check = true)
    {
        $result = null;
        $this->hook->attach('imagestyle.delete.before', $imagestyle_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDeleteImageStyle($imagestyle_id)) {
            return false;
        }

        $imagestyles = $this->config->select('imagestyles', array());
        unset($imagestyles[$imagestyle_id]);
        $this->config->set('imagestyles', $imagestyles);

        $result = true;
        $this->hook->attach('imagestyle.delete.after', $imagestyle_id, $check, $result, $this);

        return (bool) $result;
    }

    /**
     * Whether the image style can be deleted
     * @param int $imagestyle_id
     * @return bool
     */
    public function canDeleteImageStyle($imagestyle_id)
    {
        $imagestyles = $this->config->select('imagestyles', array());
        return isset($imagestyles[$imagestyle_id]);
    }

    /**
     * Returns an array of default image style data
     * @return array
     */
    protected function getDefaultData()
    {
        return array('name' => '', 'status' => false, 'actions' => array());
    }

    /**
     * Removes cached files for a given image style
     * @param integer|null $imagestyle_id
     * @return boolean
     */
    public function clearCache($imagestyle_id = null)
    {
        $result = null;
        $this->hook->attach('imagestyle.clear.cache.before', $imagestyle_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $directory = GC_DIR_IMAGE_CACHE;

        if (!empty($imagestyle_id)) {
            $directory = "$directory/$imagestyle_id";
        }

        $result = gplcart_file_delete_recursive($directory);
        $this->hook->attach('imagestyle.clear.cache.after', $imagestyle_id, $result, $this);

        return (bool) $result;
    }

    /**
     * Returns a string containing image placeholder URL
     * @param integer|null $imagestyle_id
     * @param boolean $absolute
     * @return string
     */
    public function getPlaceholder($imagestyle_id = null, $absolute = false)
    {
        $placeholder = $this->getPlaceholderPath();

        if (isset($imagestyle_id)) {
            return $this->url($imagestyle_id, $placeholder, $absolute);
        }

        return $this->url->get($placeholder, array(), true);
    }

    /**
     * Returns a relative path to image placeholder
     * @return string
     */
    public function getPlaceholderPath()
    {
        return $this->config->get('no_image', 'image/misc/no-image.png');
    }

    /**
     * Whether the path is an image placeholder
     * @param string $path
     * @return bool
     */
    public function isPlaceholder($path)
    {
        $placeholder = $this->getPlaceholderPath();
        return substr(strtok($path, '?'), -strlen($placeholder)) === $placeholder;
    }

    /**
     * Returns a string containing an image cache URL
     * @param integer $imagestyle_id
     * @param string $path
     * @param boolean $absolute
     * @return string
     */
    public function url($imagestyle_id, $path, $absolute = false)
    {
        if (empty($path)) {
            return $this->getPlaceholder($imagestyle_id, $absolute);
        }

        $normalized_path = gplcart_path_normalize($path);
        $file = GC_DIR_IMAGE_CACHE . "/$imagestyle_id/" . preg_replace('/^image\//', '', $normalized_path);
        $options = file_exists($file) ? array('v' => filemtime($file)) : array('v' => GC_TIME);

        $prefix = gplcart_path_relative(GC_DIR_IMAGE_CACHE);
        return $this->url->get("$prefix/$imagestyle_id/$normalized_path", $options, $absolute);
    }

    /**
     * Returns a string containing an image path
     * @param string $path Either relative to the root/file directory or an absolute server path
     * @return string
     */
    public function urlFromPath($path)
    {
        if (gplcart_path_is_absolute($path)) {
            $file = $path;
            $url = gplcart_path_relative($path);
        } else {

            $path = gplcart_path_normalize($path);
            $prefix = gplcart_path_relative(GC_DIR_FILE);

            if (strpos($path, "$prefix/") === 0) {
                $url = $path;
                $file = gplcart_file_absolute($path);
            } else {
                $url = "$prefix/$path";
                $file = gplcart_path_absolute($path);
            }
        }

        $query = is_file($file) ? array('v' => filemtime($file)) : array();
        return $this->url->get($url, $query, false, true);
    }

}
