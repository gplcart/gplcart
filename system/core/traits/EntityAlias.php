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
     * @param boolean $update
     * @return null
     */
    protected function setAliasTrait(\gplcart\core\models\Alias $model,
            array $data, $entity, $update = true)
    {
        if (empty($data['form']) && empty($data['alias'])) {
            return null;
        }

        if ($update) {
            $model->delete("{$entity}_id", $data["{$entity}_id"]);
        }

        if (empty($data['alias'])) {
            $data['alias'] = $model->generateEntity($data, $entity);
        }

        $model->add("{$entity}_id", $data["{$entity}_id"], $data['alias']);
    }

}
