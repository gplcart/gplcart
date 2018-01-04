<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Hook,
    gplcart\core\Config,
    gplcart\core\Handler;

/**
 * Manages basic behaviors and data related to image styles
 */
class ImageStyle
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
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
    }

    /**
     * Loads an image style
     * @param  integer $imagestyle_id
     * @return array
     */
    public function get($imagestyle_id)
    {
        $result = null;
        $this->hook->attach('image.style.get.before', $imagestyle_id, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $imagestyles = $this->getList();
        $result = isset($imagestyles[$imagestyle_id]) ? $imagestyles[$imagestyle_id] : array();
        $this->hook->attach('image.style.get.after', $imagestyle_id, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of image styles
     * @return array
     */
    public function getList()
    {
        $imagestyles = &gplcart_static('image.style.list');

        if (isset($imagestyles)) {
            return (array) $imagestyles;
        }

        $this->hook->attach('image.style.list.before', $imagestyles, $this);

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

        $this->hook->attach('image.style.list.after', $imagestyles, $this);
        return (array) $imagestyles;
    }

    /**
     * Adds an image style
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('image.style.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $default = $this->getDefaultData();
        $data += $default;

        $imagestyle_id = count($this->getList()) + 1;
        $imagestyles = $this->config->get('imagestyles', array());

        $imagestyles[$imagestyle_id] = array_intersect_key($data, $default);
        $this->config->set('imagestyles', $imagestyles);

        $this->hook->attach('image.style.add.after', $data, $imagestyle_id, $this);
        return (int) $imagestyle_id;
    }

    /**
     * Updates an image style
     * @param integer $imagestyle_id
     * @param array $data
     * @return boolean
     */
    public function update($imagestyle_id, array $data)
    {
        $result = null;
        $this->hook->attach('image.style.update.before', $imagestyle_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $default = $this->getDefaultData();
        $data += $default;

        $imagestyles = $this->config->select('imagestyles', array());
        $imagestyles[$imagestyle_id] = array_intersect_key($data, $default);
        $this->config->set('imagestyles', $imagestyles);

        $result = true;
        $this->hook->attach('image.style.update.after', $imagestyle_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes an image style
     * @param integer $imagestyle_id
     * @param bool $check
     * @return boolean
     */
    public function delete($imagestyle_id, $check = true)
    {
        $result = null;
        $this->hook->attach('image.style.delete.before', $imagestyle_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($imagestyle_id)) {
            return false;
        }

        $imagestyles = $this->config->select('imagestyles', array());
        unset($imagestyles[$imagestyle_id]);
        $this->config->set('imagestyles', $imagestyles);

        $result = true;
        $this->hook->attach('image.style.delete.after', $imagestyle_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the image style can be deleted
     * @param int $imagestyle_id
     * @return bool
     */
    public function canDelete($imagestyle_id)
    {
        $imagestyles = $this->config->select('imagestyles', array());
        return isset($imagestyles[$imagestyle_id]);
    }

    /**
     * Returns an array of image style actions
     * @param integer $imagestyle_id
     * @return array
     */
    public function getActions($imagestyle_id)
    {
        $styles = $this->getList();

        if (empty($styles[$imagestyle_id]['actions'])) {
            return array();
        }

        $actions = $styles[$imagestyle_id]['actions'];
        gplcart_array_sort($actions);
        return $actions;
    }

    /**
     * Returns an array of image style action handlers
     * @return array
     */
    public function getActionHandlers()
    {
        $handlers = &gplcart_static('image.style.action.handlers');

        if (isset($handlers)) {
            return (array) $handlers;
        }

        $handlers = (array) gplcart_config_get(GC_FILE_CONFIG_IMAGE_ACTION);
        $this->hook->attach('image.style.action.handlers', $handlers, $this);
        return (array) $handlers;
    }

    /**
     * Returns a single image action handler
     * @param string $action_id
     * @return array
     */
    public function getActionHandler($action_id)
    {
        $handlers = $this->getActionHandlers();
        return empty($handlers[$action_id]) ? array() : $handlers[$action_id];
    }

    /**
     * Apply a single action to an image file
     * @param string $source
     * @param string $target
     * @param array $handler
     * @param array $action
     * @return boolean
     */
    public function apply(&$source, &$target, $handler, &$action)
    {
        $result = null;
        $this->hook->attach('image.style.apply.before', $source, $target, $handler, $action, $result);

        if (isset($result)) {
            return (bool) $result;
        }

        try {
            $callback = Handler::get($handler, null, 'process');
            $result = call_user_func_array($callback, array(&$source, &$target, &$action));
        } catch (Exception $ex) {
            $result = false;
        }

        $this->hook->attach('image.style.apply.after', $source, $target, $handler, $action, $result);
        return (bool) $result;
    }

    /**
     * Apply an array of actions
     * @param array $actions
     * @param string $source
     * @param string $target
     * @return int
     */
    public function applyAll(array $actions, $source, $target)
    {
        $applied = 0;
        foreach ($actions as $action_id => $data) {
            $handler = $this->getActionHandler($action_id);
            if (!empty($handler)) {
                $applied += (int) $this->apply($source, $target, $handler, $data);
            }
        }

        return $applied;
    }

    /**
     * Removes cached files for a given image style
     * @param integer|null $imagestyle_id
     * @return boolean
     */
    public function clearCache($imagestyle_id = null)
    {
        $result = null;
        $this->hook->attach('image.style.clear.cache.before', $imagestyle_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = gplcart_file_delete_recursive($this->getDirectory($imagestyle_id));
        $this->hook->attach('image.style.clear.cache.after', $imagestyle_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns the full path to the image style directory
     * @param int|string $imagestyle_id
     * @return string
     */
    public function getDirectory($imagestyle_id)
    {
        return trim(GC_DIR_IMAGE_CACHE . "/$imagestyle_id", '/');
    }

    /**
     * Returns an array of default image style data
     * @return array
     */
    protected function getDefaultData()
    {
        return array(
            'name' => '',
            'status' => false,
            'actions' => array()
        );
    }

}
