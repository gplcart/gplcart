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
trait Alias
{

    /**
     * Deletes and/or adds an alias
     * @param \gplcart\core\models\Alias $model
     * @param array $data
     * @param string $entity
     * @param boolean $update
     * @return mixed
     */
    protected function setAliasTrait($model, $data, $entity, $update = true)
    {
        if (empty($data['form']) && empty($data['alias'])) {
            return null;
        }

        if ($update) {
            $model->delete("{$entity}_id", $data["{$entity}_id"]);
        }

        if (empty($data['alias'])) {
            $data['alias'] = $model->generateEntity($entity, $data);
        }

        $alias = array(
            'alias' => $data['alias'],
            'id_key' => "{$entity}_id",
            'id_value' => $data["{$entity}_id"]
        );

        return $model->add($alias);
    }

}
