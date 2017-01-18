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
     * @param array $data
     * @param string $entity
     * @param string $language
     * @return null
     */
    protected function attachTranslation(array &$data, $entity, $language)
    {
        if (empty($data)) {
            return null;
        }

        $data['language'] = 'und';
        foreach ($this->getTranslation($data["{$entity}_id"], $entity) as $translation) {
            $data['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($data['translation'][$language])) {
            $data = $data['translation'][$language] + $data;
        }
    }

    /**
     * Returns an array of translations
     * @param integer $id
     * @param string $entity
     * @return array
     */
    public function getTranslation($id, $entity)
    {
        $sql = "SELECT * FROM {$entity}_translation WHERE {$entity}_id=?";
        return $this->db->fetchAll($sql, array($id));
    }

    /**
     * Deletes and/or adds translations
     * @param array $data
     * @param string $entity
     * @param boolean $update
     * @return null
     */
    protected function setTranslation(array $data, $entity, $update = true)
    {
        if (empty($data['form']) && empty($data['translation'])) {
            return null;
        }

        if ($update) {
            $this->deleteTranslation($data["{$entity}_id"], $entity);
        }

        if (empty($data['translation'])) {
            return null;
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data["{$entity}_id"], $entity, $language, $translation);
        }
    }

    /**
     * Deletes translation(s)
     * @param integer $id
     * @param string $entity
     * @param null|string $language
     */
    public function deleteTranslation($id, $entity, $language = null)
    {
        $conditions = array("{$entity}_id" => $id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        $this->db->delete("{$entity}_translation", $conditions);
    }

    /**
     * Adds translation
     * @param integer $id
     * @param string $entity
     * @param string $language
     * @param array $translation
     */
    public function addTranslation($id, $entity, $language, array $translation)
    {
        $translation['language'] = $language;
        $translation["{$entity}_id"] = $id;

        $this->db->insert("{$entity}_translation", $translation);
    }

}
