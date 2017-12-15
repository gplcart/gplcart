<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\File as FileModel;

/**
 * Manages basic behaviors and data related to images
 */
class Image
{

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
     * @param Config $config
     * @param FileModel $file
     * @param UrlHelper $url
     */
    public function __construct(Config $config, FileModel $file, UrlHelper $url)
    {
        $this->url = $url;
        $this->file = $file;
        $this->config = $config;
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

        if (empty($options['entity_id'])) {
            return empty($options['placeholder']) ? '' : $this->getPlaceholder($options['imagestyle']);
        }

        foreach ((array) $this->getByEntity($options['entity'], $options['entity_id']) as $file) {
            if (isset($data[$options['entity'] . '_id']) && $file['entity_id'] == $data[$options['entity'] . '_id']) {
                return $this->url($file['path'], $options['imagestyle']);
            }
        }

        return empty($options['placeholder']) ? '' : $this->getPlaceholder($options['imagestyle']);
    }

    /**
     * Deletes entity images
     * @param string $entity
     * @param int $entity_id
     * @return bool
     */
    public function deleteByEntity($entity, $entity_id)
    {
        $options = array(
            'file_type' => 'image',
            'entity' => (string) $entity,
            'entity_id' => (int) $entity_id
        );

        return $this->file->deleteMultiple($options);
    }

    /**
     * Delete images by file ID(s)
     * @param int|array $file_id
     * @return bool
     */
    public function deleteByFileId($file_id)
    {
        if (empty($file_id)) {
            return false;
        }

        $options = array(
            'file_id' => $file_id,
            'file_type' => 'image'
        );

        return $this->file->deleteMultiple($options);
    }

    /**
     * Returns an array of image files for the given entity type and ID
     * @param string $entity
     * @param int|array $entity_id
     * @return array
     */
    public function getByEntity($entity, $entity_id)
    {
        $conditions = array(
            'order' => 'asc',
            'sort' => 'weight',
            'entity' => $entity,
            'file_type' => 'image',
            'entity_id' => (array) $entity_id
        );

        return (array) $this->file->getList($conditions);
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
        return $this->url($placeholder, $imagestyle_id, $absolute);
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
     * Returns a string containing the image cache URL
     * @param string $path
     * @param null|string|int $imagestyle_id
     * @param bool $absolute
     * @return string
     */
    public function url($path, $imagestyle_id = null, $absolute = false)
    {
        if (empty($path)) {
            return $this->getPlaceholder($imagestyle_id, $absolute);
        }

        if (isset($imagestyle_id)) {
            $path = GC_DIR_IMAGE_CACHE . "/$imagestyle_id/" . preg_replace('/^image\//', '', gplcart_path_normalize($path));
        }

        return $this->url->image($path);
    }

}
