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
trait Image
{

    /**
     * Set entity images
     * @param array $data
     * @param \gplcart\core\models\File $file_model
     * @param string $entity
     * @return bool
     */
    public function setImages(array &$data, $file_model, $entity)
    {
        if (empty($data['images']) || empty($data[$entity . '_id'])) {
            return false;
        }

        foreach ($data['images'] as &$image) {
            if (empty($image['file_id'])) {
                $image['entity'] = $entity;
                $image['entity_id'] = $data[$entity . '_id'];
                $image['file_id'] = (int) $file_model->add($image);
            } else {
                $file_model->update($image['file_id'], $image);
            }
        }

        return true;
    }

}
