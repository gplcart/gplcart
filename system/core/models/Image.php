<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use Exception;
use core\Hook;
use core\Config;
use core\classes\Tool;
use core\classes\Url;
use core\models\File;
use core\classes\Image as Imagestyle;

class Image
{

    /**
     * File model instance
     * @var \core\models\File $file;
     */
    protected $file;

    /**
     * Imagestyle class instance
     * @var \core\classes\Image $imagestyle
     */
    protected $imagestyle;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param File $file
     * @param Imagestyle $imagestyle
     * @param Hook $hook
     * @param Url $url
     * @param Config $config
     */
    public function __construct(File $file, Imagestyle $imagestyle, Hook $hook, Url $url, Config $config)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->file = $file;
        $this->imagestyle = $imagestyle;
        $this->config = $config;
        $this->db = $this->config->db();
    }

    /**
     * Deletes an image
     * @param integer $file_id
     * @return boolean
     */
    public function delete($file_id)
    {
        return $this->file->delete($file_id);
    }

    /**
     * Returns an array of images
     * @param string $id_key
     * @param integer $id_value
     * @return array
     */
    public function getList($id_key, $id_value)
    {
        $options = array(
            'id_key' => $id_key,
            'id_value' => $id_value,
            'sort' => 'weight',
            'file_type' => 'image',
            'order' => 'asc'
        );

        return $this->file->getList($options);
    }

    /**
     * Returns translations for a given image file
     * @param integer $file_id
     * @return array
     */
    public function getTranslations($file_id)
    {
        return $this->file->getTranslations($file_id);
    }

    /**
     * Returns a string containing an image url
     * @param integer $id
     * @param integer $imagestyle
     * @param string $id_key
     * @param array $id_value
     * @param boolean $placeholder
     * @return null|string
     */
    public function getThumb($id, $imagestyle, $id_key, array $id_value, $placeholder = true)
    {
        if (empty($id_value)) {
            return $placeholder ? $this->placeholder($imagestyle) : null; // prevent loading too many images
        }

        $images = $this->getList($id_key, $id_value); // memory cached list

        foreach ($images as $file) {
            if ($file['id_value'] == $id) {
                return $this->url($imagestyle, $file['path']);
            }
        }

        if ($placeholder) {
            return $this->placeholder($imagestyle);
        }
        
        return null;
    }

    /**
     * Adds/updates multiple images
     * @param string $id_key
     * @param integer $id_value
     * @param array $images
     * @return array
     */
    public function setMultiple($id_key, $id_value, array $images)
    {
        $result = array();
        foreach ((array) $images as $image) {
            if (empty($image['file_id'])) {
                $image['id_key'] = $id_key;
                $image['id_value'] = (int) $id_value;
                $file_id = $this->add($image);
            } else {
                $file_id = (int) $image['file_id'];
                $this->update($file_id, $image);
            }

            $result[$image['path']] = $file_id;
        }

        return $result;
    }

    /**
     * Adds an image to the database
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $data['file_type'] = 'image';
        return $this->file->add($data);
    }

    /**
     * Updates an image
     * @param integer $file_id
     * @param array $data
     * @return boolean
     */
    public function update($file_id, array $data)
    {
        return $this->file->update($file_id, $data);
    }

    /**
     * Modify an image (crop, watermark etc)
     * @param string $file
     * @param array $actions
     */
    public function modify($file, array $actions = array())
    {
        try {
            $this->applyActions($file, $actions);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Applies an array of image style actions to an image
     * @param string $file
     * @param array $actions
     */
    protected function applyActions($file, array $actions)
    {
        $this->imagestyle->setFile($file);

        foreach ($actions as $action_id => $action) {
            if (method_exists($this->imagestyle, $action_id) && $this->validateAction($action_id, $action)) {
                call_user_func_array(array($this->imagestyle, $action_id), (array) $action['value']);
            }
        }
    }

    /**
     * Returns true if the action is valid
     * @param integer $action_id
     * @param array $action
     * @return boolean
     */
    protected function validateAction($action_id, array &$action)
    {
        if ($action_id == 'overlay') {
            $action['value'][0] = GC_FILE_DIR . '/' . $action['value'][0];
            $overlay_pathinfo = pathinfo($action['value'][0]);
            $fileinfo = pathinfo($file);

            if ($overlay_pathinfo['extension'] != $fileinfo['extension']) {
                $action['value'][0] = GC_FILE_DIR . '/' . $overlay_pathinfo['filename'] . '.' . $fileinfo['extension'];
            }

            if (!file_exists($action['value'][0])) {
                return false;
            }
        }

        if ($action_id == 'text') {
            $action['value'][1] = GC_FILE_DIR . '/' . $action['value'][1];
            if (!file_exists($action['value'][1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an array of image style names
     * @return array
     */
    public function getImageStyleNames()
    {
        $names = array();
        foreach ($this->getImageStyleList() as $imagestyle_id => $imagestyle) {
            $names[$imagestyle_id] = $imagestyle['name'];
        }

        return $names;
    }

    /**
     * Returns an array of image styles
     * @return array
     */
    public function getImageStyleList()
    {
        $saved_imagestyles = $this->config->get('imagestyles', array());
        $default_imagestyles = $this->defaultImageStyle();

        $imagestyles = Tool::merge($default_imagestyles, $saved_imagestyles);

        foreach ($imagestyles as $imagestyle_id => &$imagestyle) {
            $imagestyle['imagestyle_id'] = $imagestyle_id;
            $imagestyle['in_code'] = isset($default_imagestyles[$imagestyle_id]);
        }

        $this->hook->fire('imagestyles', $imagestyles);
        return $imagestyles;
    }

    /**
     * Returns default image styles
     * @return array
     */
    protected function defaultImageStyle()
    {
        $styles = array();

        $styles[1] = array(
            'name' => '50X50',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(50, 50),
                ),
            ),
        );

        $styles[2] = array(
            'name' => '100X100',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(100, 100),
                ),
            ),
        );

        $styles[3] = array(
            'name' => '150X150',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(150, 150),
                ),
            ),
        );

        $styles[4] = array(
            'name' => '200X200',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(200, 200),
                ),
            ),
        );

        $styles[5] = array(
            'name' => '300X300',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(300, 300),
                ),
            ),
        );

        $styles[6] = array(
            'name' => '400X400',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(400, 400),
                ),
            ),
        );

        $styles[7] = array(
            'name' => '1140X400',
            'status' => 1,
            'actions' => array(
                'thumbnail' => array(
                    'weight' => 0,
                    'value' => array(1140, 380),
                ),
            ),
        );

        return $styles;
    }

    /**
     * Returns an array of imagestyle actions
     * @param integer $imagestyle_id
     * @return array
     */
    public function getImageStyleActions($imagestyle_id)
    {
        $styles = $this->getImageStyleList();

        if (empty($styles[$imagestyle_id]['actions'])) {
            return array();
        }

        $actions = $styles[$imagestyle_id]['actions'];

        Tool::sortWeight($actions);

        return $actions;
    }

    /**
     * Loads an image style
     * @param  integer $imagestyle_id
     * @return array
     */
    public function getImageStyle($imagestyle_id)
    {
        $imagestyles = $this->getImageStyleList();
        return isset($imagestyles[$imagestyle_id]) ? $imagestyles[$imagestyle_id] : array();
    }

    /**
     * Adds an imagestyle
     * @param array $data
     * @return integer
     */
    public function addImageStyle(array $data)
    {
        $this->hook->fire('add.imagestyle.before', $data);

        $imagestyles = $this->getImageStyleList();
        $imagestyle_id = $imagestyles ? (int) max(array_keys($imagestyles)) : 0;
        $imagestyle_id++;

        $imagestyles[$imagestyle_id] = $data;
        $this->config->set('imagestyles', $imagestyles);
        $this->hook->fire('add.imagestyle.after', $data, $imagestyle_id);
        return $imagestyle_id;
    }

    /**
     * Updates an imagestyle
     * @param integer $imagestyle_id
     * @param array $data
     * @return boolean
     */
    public function updateImageStyle($imagestyle_id, array $data)
    {
        $this->hook->fire('update.imagestyle.before', $imagestyle_id, $data);

        $imagestyles = $this->getImageStyleList();

        if (empty($imagestyles[$imagestyle_id])) {
            return false;
        }

        $imagestyles[$imagestyle_id] = $data;
        $this->config->set('imagestyles', $imagestyles);
        $this->hook->fire('update.imagestyle.after', $imagestyle_id, $data);
        return true;
    }

    /**
     * Deletes an imagestyle
     * @param integer $imagestyle_id
     * @return boolean
     */
    public function deleteImageStyle($imagestyle_id)
    {
        $this->hook->fire('delete.imagestyle.before', $imagestyle_id);

        $imagestyles = $this->getImageStyleList();

        if (empty($imagestyles[$imagestyle_id])) {
            return false;
        }

        unset($imagestyles[$imagestyle_id]);

        $this->config->set('imagestyles', $imagestyles);
        $this->hook->fire('delete.imagestyle.after', $imagestyle_id);
        return true;
    }

    /**
     * Removes cached files for a given imagestyle
     * @param integer|null $imagestyle_id
     * @return boolean
     */
    public function clearCache($imagestyle_id = null)
    {
        $this->hook->fire('clear.imagestyle.cache.before', $imagestyle_id);

        $directory = GC_IMAGE_CACHE_DIR;

        if ($imagestyle_id) {
            $directory = "$directory/$imagestyle_id";
        }

        $result = $this->file->deleteDirecoryRecursive($directory);
        $this->hook->fire('clear.imagestyle.cache.after', $imagestyle_id, $result);
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
     * @param boolean $check
     * @return string
     */
    public function url($imagestyle_id, $image, $absolute = false)
    {
        $image = trim($image, "/");
        $file = GC_IMAGE_CACHE_DIR . "/$imagestyle_id/" . preg_replace('/^image\//', '', $image);
        $options = file_exists($file) ? array('v' => filemtime($file)) : array('v' => GC_TIME);
        $path = "files/image/cache/$imagestyle_id/$image";
        return $this->url->get($path, $options, $absolute);
    }

    /**
     * Makes a relative to the root directory URL from the server path
     * @param string $path
     * @return string
     */
    public function urlFromPath($path)
    {
        $fullpath = GC_FILE_DIR . "/$path";
        $path = 'files/' . trim(str_replace(GC_ROOT_DIR, '', $path), "/");
        return $this->url->get($path, array('v' => filemtime($fullpath)));
    }
}
