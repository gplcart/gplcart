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
     * @param array $data
     * @param \gplcart\core\models\Alias $alias_model
     * @param string $entity
     * @param boolean $delete_existing
     * @return mixed
     */
    public function setAlias(array $data, $alias_model, $entity, $delete_existing = true)
    {
        if ((empty($data['form']) && empty($data['alias'])) || empty($data[$entity . '_id'])) {
            return null;
        }

        if ($delete_existing) {
            $alias_model->delete($entity, $data[$entity . '_id']);
        }

        if (empty($data['alias'])) {
            $data['alias'] = $alias_model->generateEntity($entity, $data);
        }

        $alias = array(
            'alias' => $data['alias'],
            'entity' => $entity,
            'entity_id' => $data[$entity . '_id']
        );

        return $alias_model->add($alias);
    }

}
