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
trait ImageTrait
{

    use \gplcart\core\traits\TranslationTrait;

    /**
     * Adds images to an entity
     * @param \gplcart\core\models\File $model
     * @param array $data
     * @param string $entity
     * @param null|string $language
     * @return null
     */
    protected function attachImagesTrait(\gplcart\core\models\File $model,
            array &$data, $entity, $language = null)
    {
        if (!empty($data)) {
            $images = $this->getImagesTrait($model, $data, "{$entity}_id");
            foreach ($images as &$image) {
                $this->attachTranslationTrait($model->getDb(), $image, 'file', $language);
            }
            $data['images'] = $images;
        }
    }

    /**
     * Returns an array of images for the given entity
     * @param \gplcart\core\models\File $model
     * @param array $data
     * @param string $key
     * @return array
     */
    protected function getImagesTrait(\gplcart\core\models\File $model,
            array $data, $key)
    {
        $options = array(
            'order' => 'asc',
            'sort' => 'weight',
            'file_type' => 'image',
            'id_key' => $key,
            'id_value' => $data[$key]
        );

        return (array) $model->getList($options);
    }

    /**
     * Set entity images
     * @param \gplcart\core\models\File $model
     * @param array $data
     * @param string $entity
     * @return array
     */
    protected function setImagesTrait(\gplcart\core\models\File $model,
            array &$data, $entity)
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
