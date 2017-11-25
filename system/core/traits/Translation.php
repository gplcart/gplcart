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
     * Adds array key containing translations to the entity
     * @param array $data
     * @param \gplcart\core\models\Translation $model
     * @param string $entity
     * @param string|null $language
     */
    public function attachTranslations(array &$data, $model, $entity, $language)
    {
        if (isset($data["{$entity}_id"])) {

            $data['language'] = 'und';
            $entity_id = $data["{$entity}_id"];
            $translations = $model->getList($entity, $entity_id, $language);

            foreach ($translations as $translation) {
                $data['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($data['translation'][$language])) {
                $data = $data['translation'][$language] + $data;
            }
        }
    }

    /**
     * Deletes and/or adds translations
     * @param array $data
     * @param \gplcart\core\models\Translation $model
     * @param string $entity
     * @param bool $delete_existing
     * @return boolean
     */
    public function setTranslations(array $data, $model, $entity, $delete_existing = true)
    {
        if (empty($data['translation']) || empty($data["{$entity}_id"])) {
            return null;
        }

        foreach ($data['translation'] as $language => $translation) {

            if ($delete_existing) {
                $model->delete($entity, $data["{$entity}_id"], $language);
            }

            $translation['language'] = $language;
            $translation["{$entity}_id"] = $data["{$entity}_id"];
            $model->add($entity, $translation);
        }

        return true;
    }

}
