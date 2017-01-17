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
 * Adds images to entities
 */
trait EntityImage
{

    /**
     * Adds images to an entity
     * @param array $entity
     * @param string $id_key
     * @param null|string $language
     * @return null
     */
    protected function attachImages(array &$entity, $id_key, $language = null)
    {
        if (empty($entity)) {
            return null;
        }

        $images = $this->getImages($entity, $id_key);
        $this->attachImageTranslation($images, $language);
        $entity['images'] = $images;
    }

    /**
     * Adds translations for images
     * @param array $images
     * @param null|string $language
     */
    protected function attachImageTranslation(array &$images, $language)
    {
        /* @var $model \gplcart\core\models\File */
        $model = Container::getInstance('gplcart\\core\\models\\File');

        foreach ($images as &$image) {
            foreach ($model->getTranslation($image['file_id']) as $translation) {
                $image['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($image['translation'][$language])) {
                $image = $image['translation'][$language] + $image;
            }
        }
    }

    /**
     * Returns an array of images for the given entity
     * @param array $entity
     * @param string $id_key
     * @return array
     */
    protected function getImages(array $entity, $id_key)
    {
        $options = array('order' => 'asc', 'sort' => 'weight', 'file_type' => 'image',
            'id_key' => $id_key, 'id_value' => $entity[$id_key]);

        /* @var $model \gplcart\core\models\File */
        $model = Container::getInstance('gplcart\\core\\models\\File');
        return (array) $model->getList($options);
    }

    /**
     * Set entity images
     * @param array $data
     * @param string $id_key
     * @param boolean $update
     * @return boolean
     */
    protected function setImages(array &$data, $id_key, $update = true)
    {
        if (empty($data['form']) && empty($data['images'])) {
            return false;
        }

        if ($update) {
            $this->deleteImages($data[$id_key], $id_key);
        }

        if (empty($data['images'])) {
            return false;
        }

        return $this->addImages($data, $id_key);
    }

    /**
     * Add an array of images
     * @param array $data
     * @param string $id_key
     * @return bool
     */
    protected function addImages(array $data, $id_key)
    {
        /* @var $model \gplcart\core\models\File */
        $model = Container::getInstance('gplcart\\core\\models\\File');

        $added = 0;
        foreach ($data['images'] as $image) {
            $image += array('id_key' => $id_key, 'id_value' => $data[$id_key]);
            $added += (int) $model->add($image);
        }

        return $added > 0;
    }

    /**
     * Deletes entity images
     * @param string $id
     * @param string $id_key
     * @return bool
     */
    protected function deleteImages($id, $id_key)
    {
        $options = array(
            'file_type' => 'image',
            'id_key' => $id_key,
            'id_value' => $id
        );

        /* @var $model \gplcart\core\models\File */
        $model = Container::getInstance('gplcart\\core\\models\\File');

        return $model->deleteMultiple($options);
    }

}
