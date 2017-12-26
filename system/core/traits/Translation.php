<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * CRUD methods for entity translation
 */
trait Translation
{

    /**
     * Deletes and/or adds translations
     * @param array $data
     * @param \gplcart\core\models\TranslationEntity $model
     * @param string $entity
     * @param bool $delete_existing
     * @return boolean
     */
    public function setTranslations(array $data, $model, $entity, $delete_existing = true)
    {
        if (empty($data['translation']) || empty($data["{$entity}_id"]) || !$model->isSupportedEntity($entity)) {
            return null;
        }

        foreach ($data['translation'] as $language => $translation) {

            if ($delete_existing) {

                $conditions = array(
                    'entity' => $entity,
                    'language' => $language,
                    "{$entity}_id" => $data["{$entity}_id"]
                );

                $model->delete($conditions);
            }

            $translation['entity'] = $entity;
            $translation['language'] = $language;
            $translation["{$entity}_id"] = $data["{$entity}_id"];

            $model->add($translation);
        }

        return true;
    }

}
