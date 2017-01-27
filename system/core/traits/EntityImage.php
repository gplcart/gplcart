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
    protected function attachImagesTrait($file, array &$data, $entity,
            $language = null)
    {
        if (!empty($data)) {
            $images = $this->getImagesTrait($file, $data, "{$entity}_id");
            $this->attachImageTranslationTrait($file, $images, $language);
            $data['images'] = $images;
        }
    }

    /**
     * Adds translations to images
     * @param \gplcart\core\models\File $file
     * @param array $images
     * @param null|string $language
     */
    protected function attachImageTranslationTrait($file, array &$images, $language)
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
    protected function getImagesTrait($file, array $data, $key)
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
     * @param \gplcart\core\models\File $model
     * @param array $data
     * @param string $entity
     * @return array
     */
    protected function setImagesTrait($model, array &$data, $entity)
    {
        if (empty($data['images'])) {
            return array();
        }

        foreach ($data['images'] as &$image) {

            if (!empty($image['file_id'])) {
                $model->update($image['file_id'], $image);
                continue;
            }

            $file = $image + array('id_key' => "{$entity}_id", 'id_value' => $data["{$entity}_id"]);
            $image['file_id'] = (int) $model->add($file);
        }

        return $data['images'];
    }

}
