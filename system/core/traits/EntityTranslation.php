<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * CRUD methods for entity translations
 */
trait EntityTranslation
{

    /**
     * Adds translations
     * @param \gplcart\core\Database $db
     * @param array $data
     * @param string $entity
     * @param string $language
     * @return null
     */
    protected function attachTranslation($db, array &$data, $entity, $language)
    {
        if (empty($data)) {
            return null;
        }

        $data['language'] = 'und';
        $translations = $this->getTranslation($db, $data["{$entity}_id"], $entity);

        foreach ($translations as $translation) {
            $data['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($data['translation'][$language])) {
            $data = $data['translation'][$language] + $data;
        }
    }

    /**
     * Returns an array of translations
     * @param \gplcart\core\Database $db
     * @param integer $id
     * @param string $entity
     * @return array
     */
    public function getTranslation($db, $id, $entity)
    {
        $sql = "SELECT * FROM {$entity}_translation WHERE {$entity}_id=?";
        return $db->fetchAll($sql, array($id));
    }

    /**
     * Deletes and/or adds translations
     * @param \gplcart\core\Database $db
     * @param array $data
     * @param string $entity
     * @param boolean $update
     * @return null
     */
    protected function setTranslation($db, array $data, $entity, $update = true)
    {
        if (empty($data['form']) && empty($data['translation'])) {
            return null;
        }

        if ($update) {
            $this->deleteTranslation($db, $data["{$entity}_id"], $entity);
        }

        if (empty($data['translation'])) {
            return null;
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($db, $data["{$entity}_id"], $entity, $language, $translation);
        }
    }

    /**
     * Deletes translation(s)
     * @param \gplcart\core\Database $db
     * @param integer $id
     * @param string $entity
     * @param null|string $language
     */
    public function deleteTranslation($db, $id, $entity, $language = null)
    {
        $conditions = array("{$entity}_id" => $id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        $db->delete("{$entity}_translation", $conditions);
    }

    /**
     * Adds translation
     * @param \gplcart\core\Database $db
     * @param integer $id
     * @param string $entity
     * @param string $language
     * @param array $translation
     */
    public function addTranslation($db, $id, $entity, $language,
            array $translation)
    {
        $translation['language'] = $language;
        $translation["{$entity}_id"] = $id;

        $db->insert("{$entity}_translation", $translation);
    }

}
