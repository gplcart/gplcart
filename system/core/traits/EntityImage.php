<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * CRUD methods for entity images
 */
trait EntityImage
{

    /**
     * Adds images to an entity
     * @param \gplcart\core\models\File $file
     * @param array $data
     * @param string $entity
     * @param null|string $language
     * @return null
     */
    protected function attachImages($file, array &$data, $entity,
            $language = null)
    {
        if (!empty($data)) {
            $images = $this->getImages($file, $data, "{$entity}_id");
            $this->attachImageTranslation($file, $images, $language);
            $data['images'] = $images;
        }
    }

    /**
     * Adds translations to images
     * @param \gplcart\core\models\File $file
     * @param array $images
     * @param null|string $language
     */
    protected function attachImageTranslation($file, array &$images, $language)
    {
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
     * @param \gplcart\core\models\File $file
     * @param array $data
     * @param string $key
     * @return array
     */
    protected function getImages($file, array $data, $key)
    {
        $options = array(
            'order' => 'asc',
            'sort' => 'weight',
            'file_type' => 'image',
            'id_key' => $key,
            'id_value' => $data[$key]
        );

        return (array) $file->getList($options);
    }

    /**
     * Set entity images
     * @param \gplcart\core\models\File $file
     * @param array $data
     * @param string $entity
     * @param boolean $update
     * @return boolean
     */
    protected function setImages($file, array &$data, $entity, $update = true)
    {
        if (empty($data['form']) && empty($data['images'])) {
            return false;
        }

        $key = "{$entity}_id";

        if ($update) {
            $this->deleteImages($file, $data[$key], $key);
        }

        if (empty($data['images'])) {
            return false;
        }

        return $this->addImages($file, $data, $key);
    }

    /**
     * Add an array of images
     * @param \gplcart\core\models\File $file
     * @param array $data
     * @param string $key
     * @return bool
     */
    protected function addImages($file, array $data, $key)
    {
        $added = 0;
        foreach ($data['images'] as $image) {
            $image += array('id_key' => $key, 'id_value' => $data[$key]);
            $added += (int) $file->add($image);
        }

        return $added > 0;
    }

    /**
     * Deletes entity images
     * @param \gplcart\core\models\File $file
     * @param string $id
     * @param string $key
     * @return bool
     */
    protected function deleteImages($file, $id, $key)
    {
        $options = array(
            'id_key' => $key,
            'id_value' => $id,
            'file_type' => 'image'
        );

        return $file->deleteMultiple($options);
    }

}
