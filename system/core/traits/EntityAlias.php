<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * CRUD methods for entity URL aliases
 */
trait EntityAlias
{

    /**
     * Deletes and/or adds an alias
     * @param \gplcart\core\models\Alias $model
     * @param array $data
     * @param string $entity
     * @param boolean $delete
     * @return null
     */
    protected function setAlias($model, array $data, $entity, $delete = true)
    {
        if (empty($data['form']) && empty($data['alias'])) {
            return null;
        }

        if ($delete) {
            $model->delete("{$entity}_id", $data["{$entity}_id"]);
        }

        $model->add("{$entity}_id", $data["{$entity}_id"], $data['alias']);
    }

    /**
     * Creates an alias
     * @param \gplcart\core\models\Alias $model
     * @param array $data
     * @param string $entity
     * @param bool $translit
     * @return string
     */
    public function createAlias($model, array $data, $entity, $translit = true)
    {
        $config = $model->getConfig();
        $pattern = $config->get("{$entity}_alias_pattern", '%t.html');
        $placeholders = $config->get("{$entity}_alias_placeholder", array('%t' => 'title'));

        return $model->generate($pattern, $placeholders, $data, $translit);
    }

}
