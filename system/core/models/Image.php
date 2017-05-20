<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache,
    gplcart\core\Handler;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to images
 */
class Image extends Model
{

    /**
     * File model instance
     * @var \gplcart\core\models\File $file;
     */
    protected $file;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language;
     */
    protected $language;

    /**
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * @param LanguageModel $language
     * @param FileModel $file
     * @param UrlHelper $url
     */
    public function __construct(LanguageModel $language, FileModel $file,
            UrlHelper $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->file = $file;
        $this->language = $language;
    }

    /**
     * Returns an array of image style action handlers
     * @return array
     */
    public function getActionHandlers()
    {
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = require GC_CONFIG_IMAGE_ACTION;

        array_walk($handlers, function(&$handler) {
            $handler['name'] = $this->language->text($handler['name']);
        });

        $this->hook->fire('imagestyle.action.handlers', $handlers, $this);
        return $handlers;
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
        $callback = Handler::get($handler, null, 'process');
        return call_user_func_array($callback, array(&$source, &$target, &$action));
    }

    /**
     * Returns a string containing an image url
     * @param array $data
     * @param array $options
     * @return string
     */
    public function getThumb(array $data, array $options)
    {
        if (empty($options['ids'])) {
            return empty($options['placeholder']) ? '' : $this->placeholder($options['imagestyle']);
        }

        $conditions = array(
            'order' => 'asc',
            'sort' => 'weight',
            'file_type' => 'image',
            'id_value' => $options['ids'],
            'id_key' => $options['id_key']
        );

        foreach ((array) $this->file->getList($conditions) as $file) {
            if ($file['id_value'] == $data[$options['id_key']]) {
                return $this->url($options['imagestyle'], $file['path']);
            }
        }

        return empty($options['placeholder']) ? '' : $this->placeholder($options['imagestyle']);
    }

    /**
     * Deletes multiple images
     * @param array $options
     * @return bool
     */
    public function deleteMultiple(array $options)
    {
        return $this->file->deleteMultiple($options);
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
     * Returns an array of image style names
     * @return array
     */
    public function getStyleNames()
    {
        $names = array();
        foreach ($this->getStyleList() as $imagestyle_id => $imagestyle) {
            $names[$imagestyle_id] = $imagestyle['name'];
        }
        return $names;
    }

    /**
     * Returns an array of image styles
     * @return array
     */
    public function getStyleList()
    {
        $default_imagestyles = require GC_CONFIG_IMAGE_STYLE;
        $saved_imagestyles = $this->config->get('imagestyles', array());
        $imagestyles = gplcart_array_merge($default_imagestyles, $saved_imagestyles);

        foreach ($imagestyles as $imagestyle_id => &$imagestyle) {
            $imagestyle['imagestyle_id'] = $imagestyle_id;
            $imagestyle['default'] = isset($default_imagestyles[$imagestyle_id]);
        }

        $this->hook->fire('imagestyle.list', $imagestyles, $this);
        return $imagestyles;
    }

    /**
     * Returns an array of imagestyle actions
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
     * Adds an imagestyle
     * @param array $data
     * @return integer
     */
    public function addStyle(array $data)
    {
        $this->hook->fire('imagestyle.add.before', $data, $this);

        $imagestyles = $this->getStyleList();
        $imagestyle_id = $imagestyles ? (int) max(array_keys($imagestyles)) : 0;
        $imagestyle_id++;

        $allowed = array('name', 'status', 'actions');
        $imagestyles[$imagestyle_id] = array_intersect_key($data, array_flip($allowed));

        $this->config->set('imagestyles', $imagestyles);

        $this->hook->fire('imagestyle.add.after', $data, $imagestyle_id, $this);
        return $imagestyle_id;
    }

    /**
     * Updates an imagestyle
     * @param integer $imagestyle_id
     * @param array $data
     * @return boolean
     */
    public function updateStyle($imagestyle_id, array $data)
    {
        $this->hook->fire('imagestyle.update.before', $imagestyle_id, $data, $this);

        $imagestyles = $this->getStyleList();

        if (empty($imagestyles[$imagestyle_id])) {
            return false;
        }

        $allowed = array('name', 'status', 'actions');
        $imagestyles[$imagestyle_id] = array_intersect_key($data, array_flip($allowed));

        $this->config->set('imagestyles', $imagestyles);

        $this->hook->fire('imagestyle.update.after', $imagestyle_id, $data, $this);
        return true;
    }

    /**
     * Deletes an imagestyle
     * @param integer $imagestyle_id
     * @return boolean
     */
    public function deleteStyle($imagestyle_id)
    {
        $this->hook->fire('imagestyle.delete.before', $imagestyle_id, $this);

        $imagestyles = $this->getStyleList();

        if (empty($imagestyles[$imagestyle_id])) {
            return false;
        }

        unset($imagestyles[$imagestyle_id]);

        $this->config->set('imagestyles', $imagestyles);
        $this->hook->fire('imagestyle.delete.after', $imagestyle_id, $this);
        return true;
    }

    /**
     * Removes cached files for a given imagestyle
     * @param integer|null $imagestyle_id
     * @return boolean
     */
    public function clearCache($imagestyle_id = null)
    {
        $this->hook->fire('imagestyle.clear.cache.before', $imagestyle_id, $this);

        $directory = GC_IMAGE_CACHE_DIR;

        if (!empty($imagestyle_id)) {
            $directory = "$directory/$imagestyle_id";
        }

        $result = gplcart_file_delete_recursive($directory);
        $this->hook->fire('imagestyle.clear.cache.after', $imagestyle_id, $result, $this);
        return $result;
    }

    /**
     * Returns a string containing image placeholder URL
     * @param integer|null $imagestyle_id
     * @param boolean $absolute
     * @return string
     */
    public function placeholder($imagestyle_id = null, $absolute = false)
    {
        $placeholder = $this->config->get('no_image', 'image/misc/no-image.png');

        if (isset($imagestyle_id)) {
            return $this->url($imagestyle_id, $placeholder, $absolute);
        }

        return $this->url->get($placeholder, array(), true);
    }

    /**
     * Returns a string containing an image cache URL
     * @param integer $imagestyle_id
     * @param string $image
     * @param boolean $absolute
     * @return string
     */
    public function url($imagestyle_id, $image, $absolute = false)
    {
        if (empty($image)) {
            return $this->placeholder($imagestyle_id, $absolute);
        }

        $trimmed = trim($image, "/");
        $file = GC_IMAGE_CACHE_DIR . "/$imagestyle_id/" . preg_replace('/^image\//', '', $trimmed);
        $options = file_exists($file) ? array('v' => filemtime($file)) : array('v' => GC_TIME);

        return $this->url->get("files/image/cache/$imagestyle_id/$trimmed", $options, $absolute);
    }

    /**
     * Makes a relative to the root directory URL from a server path
     * @param string $path
     * @return string
     */
    public function urlFromPath($path)
    {
        $query = array('v' => filemtime(GC_FILE_DIR . "/$path"));
        return $this->url->get('files/' . gplcart_relative_path($path), $query);
    }

}
