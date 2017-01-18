<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

use gplcart\core\Container;

/**
 * CRUD methods for entity images
 */
trait EntityImage
{

    /**
     * Returns File model instance
     * @return \gplcart\core\models\File
     */
    protected function getFileModel()
    {
        return Container::get('gplcart\\core\\models\\File');
    }

    /**
     * Adds images to an entity
     * @param array $data
     * @param string $entity
     * @param null|string $language
     * @return null
     */
    protected function attachImages(array &$data, $entity, $language = null)
    {
        if (empty($data)) {
            return null;
        }

        $images = $this->getImages($data, "{$entity}_id");
        $this->attachImageTranslation($images, $language);
        $data['images'] = $images;
    }

    /**
     * Adds translations for images
     * @param array $images
     * @param null|string $language
     */
    protected function attachImageTranslation(array &$images, $language)
    {
        $file = $this->getFileModel();

        foreach ($images as &$image) {
            foreach ($file->getTranslation($image['file_id']) as $translation) {
                $image['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($image['translation'][$language])) {
                $image = $image['translation'][$language] + $image;
            }
        }
    }

    /**
     * Returns an array of images for the given entity
     * @param array $data
     * @param string $key
     * @return array
     */
    protected function getImages(array $data, $key)
    {
        $options = array(
            'order' => 'asc',
            'sort' => 'weight',
            'file_type' => 'image',
            'id_key' => $key,
            'id_value' => $data[$key]
        );

        return (array) $this->getFileModel()->getList($options);
    }

    /**
     * Set entity images
     * @param array $data
     * @param string $entity
     * @param boolean $update
     * @return boolean
     */
    protected function setImages(array &$data, $entity, $update = true)
    {
        if (empty($data['form']) && empty($data['images'])) {
            return false;
        }

        $key = "{$entity}_id";

        if ($update) {
            $this->deleteImages($data[$key], $key);
        }

        if (empty($data['images'])) {
            return false;
        }

        return $this->addImages($data, $key);
    }

    /**
     * Add an array of images
     * @param array $data
     * @param string $key
     * @return bool
     */
    protected function addImages(array $data, $key)
    {
        $file = $this->getFileModel();

        $added = 0;
        foreach ($data['images'] as $image) {
            $image += array('id_key' => $key, 'id_value' => $data[$key]);
            unset($image['file_id']);
            $added += (int) $file->add($image);
        }

        return $added > 0;
    }

    /**
     * Deletes entity images
     * @param string $id
     * @param string $key
     * @return bool
     */
    protected function deleteImages($id, $key)
    {
        $options = array(
            'id_key' => $key,
            'id_value' => $id,
            'file_type' => 'image'
        );

        return $this->getFileModel()->deleteMultiple($options);
    }

}
